<?php

namespace app\api\model;

use think\Model;


class Articles extends Model
{

    // 表名
    protected $name = 'articles';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function comments()
    {
        return $this->hasMany('Comments', 'article_id');
    }



    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id', [], 'LEFT')->setEagerlyType(0);
    }
}