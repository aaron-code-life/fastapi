<?php

namespace app\api\model;

use think\Model;
use app\common\model\Sitting as SittingModel;

class Sitting extends SittingModel
{

    public function getVersionAttr($value){
        $vdata = [];
        $vdata_arr = $value;
        if(!is_array($vdata_arr)){
            $vdata_arr = json_decode($value,true);
        }
        if(is_array($vdata_arr)){
            foreach ($vdata_arr as $v){

                $vdata[]= [
                    'value'=>$v,
                    'name'=>self::VERSION_DATA[$v]
                ];
            }
            return $vdata;
        }else{
            return [];
        }
    }

    public function getSeatAttr($value){
        $sdata = [];
        $sdata_arr = $value;
        if(!is_array($value)){
            $sdata_arr = json_decode($value,true);
        }
        if(is_array($sdata_arr)){
            foreach ($sdata_arr as $v){

                $sdata[]= [
                    'value'=>$v,
                    'name'=>self::SEAT_DATA[$v]
                ];
            }
            return $sdata;
        }else{
            return [];
        }
    }

    public function getFuncAttr($value){
        $fdata = [];
        $funcdata = $value;
        if(!is_array($value)){
            $funcdata = json_decode($value,true);
        }
        if(is_array($funcdata)){
            foreach ($funcdata as $v){

                $fdata[]= [
                    'value'=>$v,
                    'name'=>self::FUNC_DATA[$v]
                ];
            }
            return $fdata;
        }else{
            return [];
        }
    }
}
