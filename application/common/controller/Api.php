<?php

namespace app\common\controller;

use app\common\library\Auth;
use think\Config;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Hook;
use think\Lang;
use think\Loader;
use think\Request;
use think\Response;
use think\Route;
use think\Validate;

/**
 * API控制器基类
 */
class Api
{

    /**
     * @var Request Request 实例
     */
    protected $request;

    /**
     *自定义扩展字段，用来补充数据库操作需要后端定义的数据库字段
     */
    protected $extendFields = [];

    /**
     * @var bool 验证失败是否抛出异常
     */
    protected $failException = false;

    /**
     * @var bool 是否批量验证
     */
    protected $batchValidate = false;

    /**
     * @var array 前置操作方法列表
     */
    protected $beforeActionList = [];

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;

    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'user_id';

    /**
     * 数据限制开启时自动填充限制字段值
     */
    protected $dataLimitFieldAutoFill = true;

    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;
    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = false;
    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = false;
    /**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $responseType = 'json';

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';

    protected $listLimit = 15;

    /**
     * 前台提交过来,需要排除的字段数据
     */
    protected $excludeFields = "";

    //请求参数
    protected $params = [];

    //新增前行为
    protected $addBeforeBehaviors = [];
    //新增后行为
    protected $addAfterBehaviors = [];
    //编辑前行为
    protected $editBeforeBehaviors = [];
    //编辑后行为
    protected $editAfterBehaviors = [];
    //删除前行为
    protected $delBeforeBehaviors = [];
    //删除后行为
    protected $delAfterBehaviors = [];

    /**
     * 导入文件首行类型
     * 支持comment/name
     * 表示注释或字段名
     */
    protected $importHeadType = 'comment';

    /**
     * 模型对象
     * @var \think\Model
     */
    protected $model = null;
    /**
     * 引入api控制器的traits
     */
    use \app\api\library\traits\Api;

    /**
     * 构造方法
     * @access public
     * @param Request $request Request 对象
     */
    public function __construct(Request $request = null)
    {

        $this->request = is_null($request) ? Request::instance() : $request;

        // 控制器初始化
        $this->_initialize();
        //如果未指定具体模型、将无法使用下列通用方法
        if(
            !$this->model &&
            in_array($this->request->action(),['add','index','del','edit','destroy','restore','multi','recyclebin'])
        ) $this->error('错误请求！');
        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                    $this->beforeAction($options) :
                    $this->beforeAction($method, $options);
            }
        }
        $this->params = $this->request->post();

    }

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        //跨域请求检测
        check_cors_request();

        // 检测IP是否允许
        check_ip_allowed();

        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');

        $this->auth = Auth::instance();

        $modulename = $this->request->module();
        $controllername = Loader::parseName($this->request->controller());
        $actionname = strtolower($this->request->action());

        // token
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', \think\Cookie::get('token')));
        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);
            //检测是否登录
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), null, 401);
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'), null, 403);
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        Config::set('upload', array_merge(Config::get('upload'), $upload));

        // 加载当前控制器语言包
        $this->loadlang($controllername);
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        $name = Loader::parseName($name);
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $this->request->langset() . '/' . str_replace('.', '/', $name) . '.php');
    }

    /**
     * 操作成功返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为1
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为0
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 前置操作
     * @access protected
     * @param string $method  前置操作方法名
     * @param array  $options 调用参数 ['only'=>[...]] 或者 ['except'=>[...]]
     * @return void
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }

            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }

            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            // 支持场景
            if (strpos($validate, '.')) {
                list($validate, $scene) = explode('.', $validate);
            }

            $v = Loader::validate($validate);

            !empty($scene) && $v->scene($scene);
        }

        // 批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }
        // 设置错误信息
        if (is_array($message)) {
            $v->message($message);
        }
        // 使用回调验证
        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }

            return $v->getError();
        }
        return true;
    }

    /**
     * 刷新Token
     */
    protected function token()
    {
        $token = $this->request->param('__token__');

        //验证Token
        if (!Validate::make()->check(['__token__' => $token], ['__token__' => 'require|token'])) {
            $this->error(__('Token verification error'), ['__token__' => $this->request->token()]);
        }

        //刷新Token
        $this->request->token();
    }

    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed   $searchfields   快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function buildparams($searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset/d", 0);
        $limit = $this->request->get("limit/d", $this->listLimit);
        //新增自动计算页码
        $page = $limit ? intval($offset / $limit) + 1 : 1;
        if ($this->request->has("page")) {
            $page = $this->request->get("page/d", 1);
        }
        $this->request->get([config('paginate.var_page') => $page]);
        $filter = (array)json_decode($filter, true);
        $op = (array)json_decode($op, true);
        $filter = $filter ? $filter : [];
        $where = [];
        $alias = [];
        $bind = [];
        $name = '';
        $aliasName = '';
        if (!empty($this->model) && $this->relationSearch) {
            $name = $this->model->getTable();
            $alias[$name] = Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $aliasName = $alias[$name] . '.';
        }
        $sortArr = explode(',', $sort);
        foreach ($sortArr as $index => & $item) {
            $item = stripos($item, ".") === false ? $aliasName . trim($item) : $item;
        }
        unset($item);
        $sort = implode(',', $sortArr);

        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $aliasName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        $index = 0;
        foreach ($filter as $k => $v) {
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $k)) {
                continue;
            }
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $aliasName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            //null和空字符串特殊处理
            if (!is_array($v)) {
                if (in_array(strtoupper($v), ['NULL', 'NOT NULL'])) {
                    $sym = strtoupper($v);
                }
                if (in_array($v, ['""', "''"])) {
                    $v = '';
                    $sym = '=';
                }
            }

            switch ($sym) {
                case '=':
                case '<>':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $v = is_array($v) ? $v : explode(',', str_replace(' ', ',', $v));
                    $findArr = array_values($v);
                    foreach ($findArr as $idx => $item) {
                        $bindName = "item_" . $index . "_" . $idx;
                        $bind[$bindName] = $item;
                        $where[] = "FIND_IN_SET(:{$bindName}, `" . str_replace('.', '`.`', $k) . "`)";
                    }
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $tableArr = explode('.', $k);
                    if (count($tableArr) > 1 && $tableArr[0] != $name && !in_array($tableArr[0], $alias) && !empty($this->model)) {
                        //修复关联模型下时间无法搜索的BUG
                        $relation = Loader::parseName($tableArr[0], 1, false);
                        $alias[$this->model->$relation()->getTable()] = $tableArr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' TIME', $arr];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
            $index++;
        }
        if (!empty($this->model)) {
            $this->model->alias($alias);
        }
        $model = $this->model;
        $where = function ($query) use ($where, $alias, $bind, &$model) {
            if (!empty($model)) {
                $model->alias($alias);
                $model->bind($bind);
            }
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit, $page, $alias, $bind];
    }

}
