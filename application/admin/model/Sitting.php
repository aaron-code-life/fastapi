<?php

namespace app\admin\model;

use app\common\model\Sitting as SittingModel;

class Sitting extends SittingModel
{

    // 追加属性
    protected $append = ['version_json','seat_json','func_json'];

    public function getVersionJsonAttr($value,$data){
        $vdata = [];
        $vdata_arr = explode(',',$data['seat']);
        if(is_array($vdata_arr)){
            foreach ($vdata_arr as $v){
                if (empty($v))continue;
                $vdata[$v]= parent::VERSION_DATA[$v];
            }
            return $vdata;
        }else{
            return [];
        }
    }

    public function getSeatJsonAttr($value,$data){
        $sdata = [];
        $sdata_arr = explode(',',$data['seat']);
        if(is_array($sdata_arr)){
            foreach ($sdata_arr as $v){
                if (empty($v))continue;
                $sdata[$v]= parent::SEAT_DATA[$v];
            }
            return $sdata;
        }else{
            return [];
        }
    }

    public function getFuncJsonAttr($value,$data){
        $fdata = [];
        $funcdata = explode(',',$data['func']);
        if(is_array($funcdata)){
            foreach ($funcdata as $v){
                if (empty($v))continue;
                $fdata[$v]= parent::FUNC_DATA[$v];
            }
            return $fdata;
        }else{
            return [];
        }
    }
}
