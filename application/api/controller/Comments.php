<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 评论接口
 */
class Comments extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * Comments模型对象
     * @var \app\api\model\Comments
     */
    protected $model = null;
    protected $modelValidate = true;//默认开启模型验证
    protected $extendFields = [];//自定义扩展字段

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\api\model\Comments;
        $this->extendFields = [
            "user_id" => $this->auth->id
        ];

    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/api/library/traits/APi.php中对应的方法复制到当前控制器,然后进行修改
     */


	/**
	* 新增评论接口
	* @ApiTitle    (新增评论接口)
	* @ApiSummary  (新增评论接口)
	* @ApiMethod   (POST)
	* @ApiRoute    (/api/comments/add)
	* @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
	* @ApiParams   (name="id", type="int", required=true, description="ID")
	* @ApiParams   (name="user_id", type="int", required=true, description="用户ID")
	* @ApiParams   (name="article_id", type="int", required=true, description="文章ID")
	* @ApiParams   (name="content", type="longtext", required=true, description="评论内容")
	* @ApiParams   (name="createtime", type="int", required=true, description="创建时间")
	* @ApiReturnParams   (name="id", type="int", required=true,sample="ID", description="ID")
	* @ApiReturnParams   (name="user_id", type="int", required=true,sample="用户ID", description="用户ID")
	* @ApiReturnParams   (name="article_id", type="int", required=true,sample="文章ID", description="文章ID")
	* @ApiReturnParams   (name="content", type="longtext", required=true,sample="评论内容", description="评论内容")
	* @ApiReturnParams   (name="createtime", type="int", required=true,sample="创建时间", description="创建时间")
	* @ApiReturn   ()
	*/
    public function add(){
        parent::add();
    }


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        $list = $this->model
                ->with(['articles','user'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

        foreach ($list as $row) {
            
            
        }

        $this->success('获取成功',$list);

    }

}
