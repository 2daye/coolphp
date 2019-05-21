<?php
return array(
    //是否开启多模块设计
    'APP_MULTI_MODULE' => true,
    //是否开启URL中控制器和操作名的自动转换
    'URL_CONVERT' => true,
    //系统默认模块
    'MODULE' => 'index',
    //系统默认控制器
    'CONTROLLER' => 'index',
    //系统默认函数
    'METHODS' => 'main',
    //系统配置路由
    'ROUTE' => [
        'abc' => ['index', 'index', 'abc']
    ],
    //框架路径
    'FP' => '/coolphp/'
);