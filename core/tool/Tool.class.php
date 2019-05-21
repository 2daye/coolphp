<?php
/**
 * CoolPHP框架主函数库
 * 存放开发中自定义的方法
 */
namespace core\tool;

//继承官方内置函数库
class Tool extends Functions
{
    //获取网站域名
    public static function getUrl()
    {
        if ($_SERVER['HTTP_HOST'] == '127.0.0.1') {
            return 'http://' . $_SERVER['HTTP_HOST'] . '/cool';
        } else {
            return 'http://' . $_SERVER['HTTP_HOST'];
        }
    }
}