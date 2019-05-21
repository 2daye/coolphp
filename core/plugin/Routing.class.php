<?php
/*
 * --路由类--
 * 1.解析网址URL
 * 2.取得执行哪个模块
 * 3.取得执行哪个控制器
 * 4.取得执行控制器里面的哪个方法
 * 5.解析URL里面的GET参数
 */
namespace core\plugin;

class Routing
{
    //储存模块
    public $module = false;
    //储存控制器
    public $controller;
    //储存控制器中的方法
    public $methods;

    //路由类构造
    public function __construct()
    {
        //判断URL是否有控制器和方法的结构 如：http://www.xxx.com/index/index/main/
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != Config::get('routing', 'FP')) {
            /*
             * explode('?', $_SERVER['REQUEST_URI'])[0]，去除?后面的GET参数
             * $_SERVER['REQUEST_URI']获取请求的URL地址，如：/ 或者 /index/index/main/
             * trim()函数，去除字符串最左边和最右边的 / 把字符串变成 index/main
             * explode()函数炸字符串，按/把字符串炸开，得到一个数组 array(0 => index, 1 => main);
             */
            $p_arr = explode('/', trim(explode('?', $_SERVER['REQUEST_URI'])[0], '/'));
            //根据路由类的配置文件，判断模块在数组的第几位
            $c_l = Config::get('routing', 'FP') != '/' ? count(explode('/', trim(Config::get('routing', 'FP'), '/'))) : 0;
            //查询有没有自定义的路由配置，如果有，优先使用自定义路由配置
            foreach (Config::get('routing', 'ROUTE') as $key => $value) {
                if ($key == $p_arr[$c_l]) {
                    foreach ($value as $k => $v) {
                        $o = $c_l + $k;
                        if ($k == 0) {
                            $p_arr[$o] = $v;
                        } else {
                            array_splice($p_arr, $o, 0, $v);
                        }
                    }
                }
            }
            //判断执行单模块还是多模块
            if (Config::get('routing', 'APP_MULTI_MODULE')) {
                //多模块设计
                $this->multi_module($p_arr, $c_l);
            } else {

                //单模块设计
                $this->single_module($p_arr, $c_l);
            }
        } else {
            //URL没有标准控制器方法格式，所以返回默认模块和控制器和默认方法
            if (Config::get('routing', 'APP_MULTI_MODULE')) {
                //开启了，多模块设计
                $this->module = Config::get('routing', 'MODULE');
            }
            $this->controller = Config::get('routing', 'CONTROLLER');
            $this->methods = Config::get('routing', 'METHODS');
        }
    }

    //多模块设计
    public function multi_module($p_arr, $c_l)
    {
        //计算模块在数组的位置
        $module_l = $c_l;
        //计算控制器在数组的位置
        $controller_l = $c_l + 1;
        //计算控制器中方法在数组的位置
        $methods_l = $c_l + 2;
        //计算get参数在数组的位置
        $get_l = $c_l + 3;
        //把模块传入module
        if (isset($p_arr[$module_l])) {
            $this->module = $p_arr[$module_l];
        } else {
            $this->module = Config::get('routing', 'MODULE');
        }
        //循环删除数组中前置的文件夹和模块
        for ($cl = 0; $cl <= $c_l; $cl++) {
            unset($p_arr[$cl]);
        }
        //把控制器传入controller
        if (isset($p_arr[$controller_l])) {
            $this->controller = $p_arr[$controller_l];
            unset($p_arr[$controller_l]);
        } else {
            $this->controller = Config::get('routing', 'CONTROLLER');
        }
        //加2得到控制器中的方法，传入methods
        if (isset($p_arr[$methods_l])) {
            $this->methods = $p_arr[$methods_l];
            unset($p_arr[$methods_l]);
        } else {
            $this->methods = Config::get('routing', 'METHODS');
        }
        //获取从第几位开始，是GET参数，加3这样就排除了，前面的 模块，控制器，方法，后面的都是get参数
        $i = $get_l;
        $get_arr_length = count($p_arr);
        for ($y = 0; $y < $get_arr_length; $y++) {
            /*
             * 在解析GET参数时，需要判断
             * 数组的下一位存不，如果不存在说明是URL参数时奇数,如：index/main/id
             * 就放弃最后一位参数的编译
             */
            if (isset($p_arr[$i + 1])) {
                $_GET[$p_arr[$i]] = $p_arr[$i + 1];
            }
            //加2，排除已经处理的get参数和值
            $i = $i + 2;
        }
    }

    //单模块设计
    public function single_module($p_arr, $c_l)
    {
        //计算控制器在数组的位置
        $controller_l = $c_l;
        //计算控制器中方法在数组的位置
        $methods_l = $c_l + 1;
        //计算get参数在数组的位置
        $get_l = $c_l + 2;
        //把控制器传入controller
        if (isset($p_arr[$controller_l])) {
            $this->controller = $p_arr[$controller_l];
            unset($p_arr[$controller_l]);
        } else {
            $this->controller = Config::get('routing', 'CONTROLLER');
        }
        //循环删除数组中前置的文件夹和模块
        for ($cl = 0; $cl <= $c_l; $cl++) {
            unset($p_arr[$cl]);
        }
        //得到控制器中的方法，传入methods
        if (isset($p_arr[$methods_l])) {
            $this->methods = $p_arr[$methods_l];
            unset($p_arr[$methods_l]);
        } else {
            $this->methods = Config::get('routing', 'METHODS');
        }
        //解析后面的GET参数
        $i = $get_l;
        $get_arr_length = count($p_arr);
        for ($y = 0; $y < $get_arr_length; $y++) {
            /*
             * 在解析GET参数时，需要判断
             * 数组的下一位存不，如果不存在说明是URL参数时奇数,如：index/main/id
             * 就放弃最后一位参数的编译
             */
            if (isset($p_arr[$i + 1])) {
                $_GET[$p_arr[$i]] = $p_arr[$i + 1];
            }
            //加2，排除已经处理的get参数和值
            $i = $i + 2;
        }
    }

}