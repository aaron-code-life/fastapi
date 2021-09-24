<?php

namespace app\api\behavior;

use app\common\behavior\Common;

class Sitting extends Common
{

    public function beforeAddSitting(&$params)
    {
        if(session('admin_pass') != config("site.admin_pass")){
            result('请先提供管理员密码');
        }else{
            if(isset($params['version'])) $params['version'] = json_encode($params['version'],JSON_UNESCAPED_UNICODE);
            if(isset($params['seat'])) $params['seat'] = json_encode($params['seat'],JSON_UNESCAPED_UNICODE);
            if(isset($params['func'])) $params['func'] = json_encode($params['func'],JSON_UNESCAPED_UNICODE);
        }
        return $this->result;
    }

    public function afterAddSitting(&$params,$extra)
    {
        session('admin_pass',null);
        return $this->result;
    }

}
