<?php

namespace app\api\behavior;

use app\common\behavior\Common;

class Feedback extends Common
{

    public function beforeAddFeedback(&$params)
    {
        if(isset($params['imgs'])) $params['imgs'] = json_encode($params['imgs'],JSON_UNESCAPED_UNICODE);
        return $this->result;
    }

}
