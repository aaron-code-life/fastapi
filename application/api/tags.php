<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用行为扩展定义文件
return [
    //文章新增前行为
    'before_add_articles' => [
        'app\\api\\behavior\\Articles',
    ],
    //文章新增后行为
    'after_add_articles' => [
        'app\\api\\behavior\\Articles',
    ],
    //评论新增前行为
    'before_add_comments' => [
        'app\\api\\behavior\\Comments',
    ],
    //评论新增后行为
    'after_add_comments' => [
        'app\\api\\behavior\\Comments',
    ],
];
