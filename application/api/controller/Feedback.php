<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 意见反馈接口
 */
class Feedback extends Api
{


    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    /**
     * Feedback模型对象
     * @var \app\api\model\Feedback
     */
    protected $model = null;
    protected $modelValidate = true;//默认开启模型验证
    protected $extendFields = [];//自定义扩展字段，用来补充数据库操作需要后端定义的数据库字段
    protected $addBeforeBehaviors = ['before_add_feedback'];
    //请求参数
    protected $params = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\api\model\Feedback;
        $this->extendFields = [
            "user_id" => $this->auth->id
        ];
    }

    /**
     * 意见反馈新增接口
     * @ApiTitle    (意见反馈新增接口)
     * @ApiSummary  (意见反馈新增接口)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/feedback/add)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="type", type="int", required=true, description="反馈类别1、下载/加载问题2、图片问题3、bug反馈")
     * @ApiParams   (name="content", type="string", required=true, description="反馈内容")
     * @ApiParams   (name="imgs", type="array", required=false, description="图片数组")
     * @ApiParams   (name="contact", type="string", required=false, description="联系方式")
     * @ApiReturn   ({
        "code": 1,
        "msg": "操作成功",
        "time": "1631869837",
        "data": {
        "id": "3",
        "user_id": 1,
        "type": "2",
        "content": "你好我的椅子不能连接蓝牙，点击没有任何反应",
        "imgs": "",
        "contact": "",
        "createtime": "2021-09-17 16:50:19"
        }
    })
     */
    public function add(){
        //需要自定义处理请新增对应的行为
        parent::add();
    }

}
