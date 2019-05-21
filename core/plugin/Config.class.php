<?php
/*
 * 配置类
 * */

namespace core\plugin;

class Config
{
    //定义静态配置数组变量存放配置，避免重复加载配置文件，影响性能
    public static $conf = array();

    public static function get($file, $name)
    {
        //判断配置数组中是否存在此配置项，如果存在，直接返回
        if (isset(self::$conf[$file][$name])) {
            return self::$conf[$file][$name];
        } else {
            //不存在，执行加载配置文件
            $files = ROOT_PATH . '/core/config/' . $file . '.php';
            if (is_file($files)) {
                $config = include $files;
                if (isset($config[$name])) {
                    //读出配置项，存入配置数组
                    self::$conf[$file][$name] = $config[$name];
                    return $config[$name];
                } else {
                    throw new \Exception($name . '配置项不存在');
                }
            } else {
                throw new \Exception($file . '配置文件不存在');
            }
        }
    }

    public static function get_all($file)
    {
        if (isset(self::$conf[$file])) {
            return self::$conf[$file];
        } else {
            $files = ROOT_PATH . '/core/config/' . $file . '.php';
            if (is_file($files)) {
                $config = include $files;
                self::$conf[$file] = $config;
                return self::$conf[$file];
            } else {
                throw new \Exception($file . '配置文件不存在');
            }
        }
    }
}