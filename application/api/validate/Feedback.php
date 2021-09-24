<?php

namespace app\api\validate;

use think\Validate;

class Feedback extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'type' => 'require|in:1,2,3',
        'content' => 'require|min:10',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'type.require'=>'反馈类别未选择',
        'type.in'=>'反馈类别错误',
        'content.require'=>'反馈内容必填',
        'content.min'=>'反馈内容不能少于10个字',
        //'imgs.imgs'=>'图片必须是数组',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['type','content','imgs'],
        'edit'  => ['type','content','imgs'],
    ];
    
}
