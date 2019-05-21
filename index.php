<?php
/*
 * CoolPhp框架入口文件
 * */
//打开session
session_start();
//设置页面中文编码集
header("Content-Type:text/html;charset=utf-8");
//设置时区为中国PRC
date_default_timezone_set("PRC");
//获取项目根目录
define("ROOT_PATH", dirname(__FILE__));
//是否开启框架的debug模式
define("DEBUG", true);
//加载CoolPHP框架核心类
include ROOT_PATH . '/core/CoolPhp.class.php';
//自动加载函数，当new一个不存在的类的时候，自动加载定义的函数
spl_autoload_register("\\core\\CoolPhp::load");
//设置PHP报错等级，0表示将错误顶级设置为完全不报错,只报致命错误E_ALL & ~E_NOTICE
if (DEBUG) {
    //设置任何错误都提示
    error_reporting();
    //使用Whoops提供的优美PHP错误展示框架
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
} else {
    //设置任何错误都不提示
    error_reporting(0);
}
//启动框架
\core\CoolPhp::run();
