<?php

namespace app\api\behavior;

use app\common\behavior\Common;

class Comments extends Common
{

    public function beforeAddComments(&$params)
    {
        //TODO::评论之前的操作，检测敏感词或者自动过滤不文明信息
        return $this->result;
    }

    public function afterAddComments(&$params,$extra)
    {
        //TODO::评论之后自动向文章作者发送站内信息或者邮件
        return $this->result;
    }

}
