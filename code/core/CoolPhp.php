<?php
/**
 * CoolPHP框架的核心类
 * @author 2daye
 */

namespace core;

use core\tool\Tool;
use core\plugin\Log;
use core\plugin\Config;
use core\plugin\Routing;
use core\plugin\Request;

class CoolPhp
{
    /**
     * 框架运行方法
     * @return void
     * @throws \Exception
     * @author 2daye
     */
    public static function run()
    {
        // 初始化Log类
        Log::init();

        // new 出路由类，获取url调用了什么控制器和控制器中的什么方法
        $routing = new Routing();

        // 判断是否需要转换命名风格
        $convert = Config::get('routing', 'URL_CONVERT');

        $c = $convert ? self::camelCase($routing->controller) : $routing->controller;
        $m = $convert ? strtolower($routing->methods) : $routing->methods;

        // 判断使用多模块运行，还是单模块
        if ($routing->module) {
            self::multiRun($routing->module, $c, $m);
        } else {
            self::singleRun($c, $m);
        }
    }

    /**
     * 多模块模式
     * @param $routingModule
     * @param $routingController
     * @param $routingMethods
     * @return void
     * @throws \Exception
     * @author 2daye
     */
    public static function multiRun($routingModule, $routingController, $routingMethods)
    {
        // 拼接模块文件夹
        $module = ROOT_PATH . '/core/app/' . $routingModule;

        // 拼接控制器
        $controllerClass = '\core\app\\' . $routingModule . '\controller\\' . $routingController;

        // 拼接控制器文件
        $controllerClassFile = ROOT_PATH . '/core/app/' . $routingModule . '/controller/' . $routingController . '.php';

        // 获取使用什么方法
        $controllerAction = $routingMethods;

        // 判断模块存在吗
        if (is_dir($module) && $routingModule != 'common') {
            // 判断控制器文件存在吗
            if (is_file($controllerClassFile)) {
                // 控制器存在直接new出控制器
                $controller = new $controllerClass();

                // method_exists()判断控制器中的一个方法是否存在
                if (method_exists($controller, $controllerAction)) {
                    //把模块/控制器/操作方法名称传入Request类
                    $request = Request::getInstance();
                    $request->module = $routingModule;
                    $request->controller = $routingController;
                    $request->methods = $routingMethods;

                    //方法存在，就执行这个方法
                    $controller->$controllerAction();

                    //打上日志，执行了什么控制器和控制器的什么方法
                    //\core\plugin\Log::log('module->' . $routingModule . '   controller->' . $routingController . '   methods->' . $routing_methods);
                } else {
                    if (DEBUG) {
                        throw new \Exception($controllerAction . '，是一个不存在的方法');
                    } else {
                        Tool::show404();
                    }
                }
            } else {
                if (DEBUG) {
                    throw new \Exception($controllerClass . '，是一个不存在的控制器');
                } else {
                    Tool::show404();
                }
            }
        } else {
            if (DEBUG) {
                throw new \Exception($routingModule . '，是一个不存在的模块');
            } else {
                Tool::show404();
            }
        }
    }

    /**
     * 单模块模式
     * @param $routingController
     * @param $routingMethods
     * @return void
     * @throws \Exception
     * @author 2daye
     */
    public static function singleRun($routingController, $routingMethods)
    {
        // 拼接控制器
        $controllerClass = '\core\app\controller\\' . $routingController;

        // 拼接控制器文件
        $controllerClassFile = ROOT_PATH . '/core/app/controller/' . $routingController . '.php';

        // 获取使用什么方法
        $controllerAction = $routingMethods;

        // 判断控制器文件存在吗
        if (is_file($controllerClassFile)) {
            // 控制器存在直接new出控制器
            $controller = new $controllerClass();

            // method_exists()判断控制器中的一个方法是否存在
            if (method_exists($controller, $controllerAction)) {
                // 把控制器/操作方法名称传入Request类
                $request = Request::getInstance();
                $request->controller = $routingController;
                $request->methods = $routingMethods;

                // 方法存在执行这个方法
                $controller->$controllerAction();

                // 打上日志，执行了什么控制器和控制器的什么方法
                //\core\plugin\Log::log('controller->' . $routing_controller . '   methods->' . $routing_methods);
            } else {
                if (DEBUG) {
                    throw new \Exception($controllerAction . '，是一个不存在的方法');
                } else {
                    Tool::show404();
                }
            }
        } else {
            if (DEBUG) {
                throw new \Exception($controllerClass . '，是一个不存在的控制器');
            } else {
                Tool::show404();
            }
        }
    }

    /**
     * 引入框架的类
     * @param $class
     * @return void
     * @author 2daye
     */
    public static function load($class)
    {
        $coolClass = str_replace('\\', '/', $class);

        if (is_file(ROOT_PATH . '/' . $coolClass . '.php')) {
            include_once ROOT_PATH . '/' . $coolClass . '.php';
        } elseif (is_file(ROOT_PATH . '/core/plugin/' . $coolClass . '.php')) {
            include_once ROOT_PATH . '/core/plugin/' . $coolClass . '.php';
        }
    }

    /**
     * 字符串命名风格转换（驼峰规则，首字母大写）
     * @param string $string 要转换的字符串
     * @return string
     * @author 2daye
     */
    public static function camelCase(string $string): string
    {
        // preg_replace_callback()执行一个正则表达式搜索并且使用一个回调进行替换
        $string = preg_replace_callback(
        // 正则匹配_后面的第一个字母
            '/_([a-zA-Z])/',
            // 得到搜索配结果数组
            function ($match) {
                // strtoupper()把所有字符转换为大写
                return strtoupper($match[1]);
            },
            // 传入要替换的字符串
            $string
        );

        // ucfirst()把字符串的首字符转换为大写，得到最终的大驼峰命名规则字符串(首字母大写)
        return ucfirst($string);
    }
}