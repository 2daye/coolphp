<?php
/**
 * 请求类
 * 作者：2daye
 */
namespace core\plugin;

class Request
{
    public $module = null;
    public $controller = null;
    public $methods = null;

    //定义$instance用于存放实例化的对象
    private static $instance;

    //静态单例模式
    public static function getInstance()
    {
        /**
         * 通过使用 instanceof操作符 和 self关键字
         * 可以检测到类是否已经被实例化
         * 如果 $instance 没有保存，类本身的实例。
         */
        if (!(self::$instance instanceof self)) {
            //就把本身的实例赋给 $instance
            self::$instance = new self();
        }
        return self::$instance;
    }

    //私有构造函数，实现单例
    private function __construct()
    {
    }

    //单例防止克隆
    private function __clone()
    {
    }

    /**
     * 获取当前框架根路径
     * @return string
     */
    public function frameworkRootPath()
    {
        $frameworkPath = Config::get('routing', 'FP') != '/' ? Config::get('routing', 'FP') : '';
        return $this->networkingProtocol() . '://' . $_SERVER['HTTP_HOST'] . $frameworkPath;
    }

    /**
     * 获取当前域名
     * @return string
     */
    public function domainName()
    {
        return $this->networkingProtocol() . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * 当前是否是ssl
     * @return bool
     */
    public function isSsl()
    {
        if (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']) {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && 'off' !== strtolower($_SERVER['HTTP_FRONT_END_HTTPS'])) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前网络请求协议
     * @return string
     */
    public function networkingProtocol()
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     * 获取当前请求的模块
     * @return string
     */
    public function module()
    {
        return $this->module !== null ? $this->module : '';
    }

    /**
     * 获取当前请求的控制器
     * @return string
     */
    public function controller()
    {
        return $this->controller !== null ? $this->controller : '';
    }

    /**
     * 获取当前请求的方法
     * @return string
     */
    public function methods()
    {
        return $this->methods !== null ? $this->methods : '';
    }

    /**
     * 判断当前是否是ajax请求
     * @return bool
     */
    public function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    /**
     * 判断当前是否是get请求
     * @return bool
     */
    public function isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET') ? true : false;
    }

    /**
     * 判断当前是否是post请求
     * @return bool
     */
    public function isPost()
    {
        return (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') ? true : false;
    }
}
