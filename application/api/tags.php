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
    //座椅配置新增前行为
    'before_add_sitting' => [
        'app\\api\\behavior\\Sitting',
    ],
    //座椅配置新增后行为
    'after_add_sitting' => [
        'app\\api\\behavior\\Sitting',
    ],
    //意见反馈新增前行为
    'before_add_feedback' => [
        'app\\api\\behavior\\Feedback',
    ],
];
