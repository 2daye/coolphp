<?php
/**
 * 模板类 - 继承Smarty
 * 1.重写Smarty默认输出符号{}，避免和js的{}符号冲突
 * 2.重写Smarty默认模板路径
 * 3.重写Smarty默认模板缓存路径
 */
namespace core\plugin;

//引入使用Smatry引擎模板
include ROOT_PATH . "/core/plugin/Smarty/Smarty.class.php";

//创建Template类，extends关键字继承 Smarty
class Template extends \Smarty
{
    //定义$instance用于存放实例化的对象
    private static $instance;

    //静态单例模式
    public static function getInstance()
    {
        /**
         * 通过使用 instanceof操作符 和 self关键字 ，
         * 可以检测到类是否已经被实例化，如果 $instance 没有保存，类本身的实例。
         */
        if (!(self::$instance instanceof self)) {
            //就把本身的实例赋给 $instance
            self::$instance = new self();
        }
        return self::$instance;
    }

    //构造方法继承Smarty构造
    public function __construct()
    {
        //调用Smarty的构造方法
        parent::__construct();
        //重写Smarty配置
        $this->rewriteSmartyConfig();
    }

    //单例防止克隆
    private function __clone()
    {
    }

    /**
     * 重写 Smarty
     * 模板/缓存，目录
     * 重写 Smarty原始模板输出符号{}
     */
    private function rewriteSmartyConfig()
    {
        //重写Smarty模板数据传输符号，如果用Smarty原始的，后期项目可能会有{}符号的冲突
        $this->left_delimiter = '<{';
        $this->right_delimiter = '}>';
        $this->setTemplateDir(ROOT_PATH . "/core/app");
        $this->setCompileDir(ROOT_PATH . '/core/cache/smarty');
    }
}