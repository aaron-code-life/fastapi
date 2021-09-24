<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 配置设置接口
 */
class Sitting extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = [];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    /**
     * Sitting模型对象
     * @var \app\api\model\Sitting
     */
    protected $model = null;
    protected $modelValidate = true;//默认开启模型验证
    protected $extendFields = [];//自定义扩展字段

    protected $addBeforeBehaviors = ['before_add_sitting'];//新增前行为
    protected $addAfterBehaviors = ['after_add_sitting'];//新增后行为

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\api\model\Sitting;
        //扩展字段
        $this->extendFields = [
            "user_id" => $this->auth->id
        ];
    }

	/**
	* 新增配置设置接口
	* @ApiTitle    (新增配置设置接口)
	* @ApiSummary  (新增配置设置接口)
	* @ApiMethod   (POST)
	* @ApiRoute    (/api/sitting/add)
	* @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
	* @ApiParams   (name="version", type="array", required=true, description="版本号1豪华2舒适3旗舰")
	* @ApiParams   (name="seat", type="array", required=true, description="座椅1左前2右前3左后4右后")
	* @ApiParams   (name="func", type="array", required=true, description="功能1通风2加热")
	* @ApiParams   (name="phone", type="varchar", required=true, description="手机号")
	* @ApiReturnParams   (name="id", type="int", required=true,sample="ID", description="ID")
	* @ApiReturnParams   (name="user_id", type="int", required=true,sample="用户ID", description="用户ID")
	* @ApiReturnParams   (name="version", type="varchar", required=true,sample="版本号1豪华2舒适3旗舰", description="版本号1豪华2舒适3旗舰")
	* @ApiReturnParams   (name="seat", type="varchar", required=true,sample="座椅1左前2右前3左后4右后", description="座椅1左前2右前3左后4右后")
	* @ApiReturnParams   (name="func", type="varchar", required=true,sample="功能1通风2加热", description="功能1通风2加热")
	* @ApiReturnParams   (name="phone", type="varchar", required=true,sample="手机号", description="手机号")
	* @ApiReturnParams   (name="createtime", type="int", required=true,sample="创建时间", description="创建时间")
	* @ApiReturn   ({
    "code": 1,
    "msg": "操作成功",
    "time": "1632387468",
    "data": null
    })
	*/
    public function add(){
        parent::add();
    }


    /**
     * 配置设置列表接口
     * @ApiTitle    (配置设置列表接口)
     * @ApiSummary  (配置设置列表接口)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/sitting/index)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="version", type="array", required=true, description="版本号1豪华2舒适3旗舰")
     * @ApiParams   (name="seat", type="array", required=true, description="座椅1左前2右前3左后4右后")
     * @ApiParams   (name="func", type="array", required=true, description="功能1通风2加热")
     * @ApiParams   (name="phone", type="varchar", required=true, description="手机号")
     * @ApiReturnParams   (name="id", type="int", required=true,sample="ID", description="ID")
     * @ApiReturnParams   (name="user_id", type="int", required=true,sample="用户ID", description="用户ID")
     * @ApiReturnParams   (name="version", type="varchar", required=true,sample="版本号1豪华2舒适3旗舰", description="版本号1豪华2舒适3旗舰")
     * @ApiReturnParams   (name="seat", type="varchar", required=true,sample="座椅1左前2右前3左后4右后", description="座椅1左前2右前3左后4右后")
     * @ApiReturnParams   (name="func", type="varchar", required=true,sample="功能1通风2加热", description="功能1通风2加热")
     * @ApiReturnParams   (name="phone", type="varchar", required=true,sample="手机号", description="手机号")
     * @ApiReturnParams   (name="createtime", type="int", required=true,sample="创建时间", description="创建时间")
     * @ApiReturn   ({
    "code": 1,
    "msg": "操作成功",
    "time": "1632387468",
    "data": null
    })
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        //写在这里

        $list = $this->model
                ->where($where)
                ->where('user_id', $this->auth->id)
                ->order($sort, $order)
                ->paginate($limit);

        $this->success('获取成功',$list);

    }

}
