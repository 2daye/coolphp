<?php
/**
 * Log日志类
 * 作者：2daye
 */
namespace core\plugin;

class Log
{
    static $class;

    public static function init($way = null)
    {
        //判断是否有传入日志方案，没有就用配置的方法
        $drive = $way != null ? $way : \core\plugin\Config::get('log', 'LOG_DRIVE');
        //call_user_func() 方法可以把字符串当方法调用
        self::$class = call_user_func('\core\plugin\Drive\log\\' . $drive . '::getInstance');
    }

    public static function log($name, $logfilename = 'log')
    {
        self::$class->log($name, $logfilename);
    }
}