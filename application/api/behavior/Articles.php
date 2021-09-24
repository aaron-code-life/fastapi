<?php

namespace app\api\behavior;

use app\common\behavior\Common;

class Articles extends Common
{

    public function beforeAddArticle(&$params)
    {
        //TODO::新增文章之前的操作，比如检测有没有敏感词或者根据不同的用户给其设置不同的文章页脚
        return $this->result;
    }

    public function afterAddArticle(&$params,$extra)
    {
        //TODO::新增文章之后的操作，比如发布后发邮件通知其粉丝，或者增加其积分经验
        return $this->result;
    }

}
