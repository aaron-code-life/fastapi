<?php

namespace app\api\library\traits;

use app\admin\library\Auth;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Hook;

trait Api
{

    /**
     * 排除前台提交过来的字段
     * @param $params
     * @return array
     */
    protected function preExcludeFields($params)
    {
        if (is_array($this->excludeFields)) {
            foreach ($this->excludeFields as $field) {
                if (key_exists($field, $params)) {
                    unset($params[$field]);
                }
            }
        } else {
            if (key_exists($this->excludeFields, $params)) {
                unset($params[$this->excludeFields]);
            }
        }
        return $params;
    }


    /**
     * 查看
     */
    protected function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);

        $this->success('获取成功',$list);
    }

    /**
     * 添加
     */
    protected function add()
    {
        if ($this->request->isPost()) {
            $params = $this->params;
            if ($params) {
                if(!is_array($this->addBeforeBehaviors)){
                    $this->addBeforeBehaviors = [$this->addBeforeBehaviors];
                }
                //执行新增前的行为
                foreach ($this->addBeforeBehaviors as $before){
                    Hook::listen($before, $params,null, true);
                }
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }

                    $save_data = array_merge($params,$this->extendFields);
                    $result = $this->model->allowField(true)->save($save_data);
                    if(!is_array($this->addAfterBehaviors)){
                        $this->addAfterBehaviors = [$this->addAfterBehaviors];
                    }
                    //执行新增后的行为
                    foreach ($this->addAfterBehaviors as $after){
                        $hook_result = Hook::listen($after, $params, $result, true);
                        if($hook_result['result'] !== true){
                            throw new \think\Exception($hook_result['msg']);
                        }
                    }
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success('操作成功');
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->error(_('请求方式错误！'));
    }

    /**
     * 编辑
     */
    protected function edit($id = null)
    {
        $id = $id ? $id : $this->params['id'];

        $row = $this->model->where($this->dataLimitField,$this->auth->id)->find($id);

        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $params = $this->params;
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success('操作成功',$row);
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->success('操作成功');
    }

    /**
     * 批量更新
     */
    protected function multi($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->params['ids'];
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->params['params'], $values);
                $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values) {
                    $this->model->where($this->dataLimitField, '=', $this->auth->id);
                    $count = 0;
                    Db::startTrans();
                    try {
                        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                        foreach ($list as $index => $item) {
                            $count += $item->allowField(true)->isUpdate(true)->save($values);
                        }
                        Db::commit();
                    } catch (PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    if ($count) {
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 删除
     */
    protected function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }

        $ids = $ids ? $ids : $this->request->post("ids");

        if ($ids) {
            $pk = $this->model->getPk();

            $this->model->where($this->dataLimitField, '=', $this->auth->id);

            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 真实删除
     */
    protected function destroy($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $pk = $this->model->getPk();
        $this->model->where($this->dataLimitField, '=', $this->auth->id);
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->onlyTrashed()->select();
            foreach ($list as $k => $v) {
                $count += $v->delete(true);
            }
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        } else {
            $this->error(__('No rows were deleted'));
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 回收站
     */
    protected function recyclebin()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        list($where, $sort, $order, $offset, $limit,$page) = $this->buildparams();
        $list = $this->model
            ->onlyTrashed()
            ->where($where)
            ->order($sort, $order)
            ->paginate($page);
        $this->success('获取成功',$list);
    }

    /**
     * 还原
     */
    protected function restore($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->params['ids'];
        $pk = $this->model->getPk();
        $this->model->where($this->dataLimitField, '=', $this->auth->id);
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->onlyTrashed()->select();
            foreach ($list as $index => $item) {
                $count += $item->restore();
            }
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }



}
