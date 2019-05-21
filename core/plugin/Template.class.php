<?php
/*
 * 模板类 - 继承Smarty
 * 1.重写Smarty默认输出符号{}，避免和js的{}符号冲突
 * 2.重写Smarty默认模板路径
 * 3.重写Smarty默认模板缓存路径
 * */

//命名空间
namespace core\plugin;

//引入使用Smatry引擎模板
include ROOT_PATH . "/core/plugin/Smarty/Smarty.class.php";

//创建Template类，extends关键字继承 Smarty
class Template extends \Smarty
{
    //构造方法继承smarty构造
    public function __construct()
    {
        //调用smarty的构造方法;
        parent::__construct();
        //定义函数方法rewrite_smarty_config()，重写Smarty配置
        $this->rewrite_smarty_config();
    }

    /*
     * 重写 Smarty
     * 模板/缓存，目录
     * 重写 Smarty原始模板输出符号{}
     * */
    private function rewrite_smarty_config()
    {
        //重写Smarty模板数据传输符号，如果用Smarty原始的，后期项目可能会有{}符号的冲突
        $this->left_delimiter = '<{';
        $this->right_delimiter = '}>';
        $this->setTemplateDir(ROOT_PATH . "/core/app");
        $this->setCompileDir(ROOT_PATH . '/core/cache/smarty');
    }
}