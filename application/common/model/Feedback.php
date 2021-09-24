<?php

namespace app\common\model;

use think\Model;


class Feedback extends Model
{

    // 表名
    protected $name = 'feedback';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;



    const LOAD_QUS = 1;
    const IMG_QUS = 2;
    const BUG_QUS = 3;
    const FEED_TYPE_DATA = [
        self::LOAD_QUS => '下载/加载问题',
        self::IMG_QUS => '图片问题',
        self::BUG_QUS => 'BUG反馈'
    ];

    // 追加属性
    protected $append = [
        'type_text'
    ];

    public function getTypeTextAttr($value,$data){
        return self::FEED_TYPE_DATA[$data['type']];
    }

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
