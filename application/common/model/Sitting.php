<?php

namespace app\common\model;

use think\Model;


class Sitting extends Model
{

    // 表名
    protected $name = 'sitting';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    /*座椅版本*/
    const LUXURY = 1;//豪华
    const COMFORT = 2;//舒适
    const FLAGSHIP = 3;//旗舰
    const VERSION_DATA = [
        self::LUXURY => '豪华',
        self::COMFORT => '舒适',
        self::FLAGSHIP => '旗舰',
    ];

    /*座椅位置*/
    const LF = 1;//左前
    const RF = 2;//右前
    const LB = 3;//左后
    const RB = 4;//右后
    const SEAT_DATA = [
        self::LF => '左前',
        self::RF => '右前',
        self::LB => '左后',
        self::RB => '右后',
    ];

    /*功能*/
    const TF = 1;
    const JR = 2;
    const FUNC_DATA = [
        self::TF => '通风',
        self::RF => '加热',
    ];


    // 追加属性
    protected $append = [
    ];



    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->alias(['a','b'])->setEagerlyType(0);
    }
}
