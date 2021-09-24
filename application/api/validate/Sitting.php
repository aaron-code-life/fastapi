<?php

namespace app\api\validate;

use think\Validate;

class Sitting extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'version' => 'require',
        'seat' => 'require',
        'func' => 'require',
        'phone' => 'require|regex:^1\d{10}$',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'version.require'=>'版本号未选择',
        'seat.require'=>'座椅未选择',
        'func.require'=>'功能未选择',
        'phone.require'=>'手机号未设置',
        'phone.regex'=>'手机号格式错误',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['version','seat','func','phone'],
        'edit'  => ['version','seat','func','phone'],
    ];
    
}
