<?php
/*
 * Log日志类
 * */
namespace core\plugin;

class Log
{
    static $class;

    public static function init()
    {
        //获取使用什么Log驱动
        $drive = \core\plugin\Config::get('log', 'LOG_DRIVE');
        $driveclass = '\core\plugin\Drive\log\\' . $drive;
        self::$class = new $driveclass;
    }

    public static function log($name, $logfilename = 'log')
    {
        self::$class->log($name, $logfilename);
    }
}