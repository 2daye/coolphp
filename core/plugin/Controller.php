<?php
/**
 * 控制器父类
 * @author 2daye
 */

namespace core\plugin;

class Controller
{
    protected $frameworkRootPath = '';

    /**
     * 控制器父类的构造方法
     * @author 2daye
     */
    public function __construct()
    {
        // 获取设置框架根路径
        $request = Request::getInstance();
        $this->frameworkRootPath = $request->frameworkRootPath();

        // 控制器初始化方法
        $this->_init();
    }

    /**
     * 控制器初始化
     * @return void
     * @author 2daye
     */
    protected function _init()
    {
    }

    /**
     * 调用模板类assign方法/传递数据到模板
     * @param $parameter
     * @param $value
     * @return void
     * @author 2daye
     */
    protected function assign($parameter, $value)
    {
        $template = Template::getInstance();
        $template->assign($parameter, $value);
    }

    /**
     * 调用模板类的display方法/渲染html模板
     * @param string $html 要渲染的html模板
     * @return void
     * @throws \SmartyException
     * @author 2daye
     */
    protected function display(string $html = '')
    {
        $request = Request::getInstance();
        if ('' == $html) {
            $html = $request->module . '/view/' . strtolower($request->controller) . '/' . strtolower($request->methods) . '.html';
        }
        $template = Template::getInstance();
        $template->assign('frameworkRootPath', $this->frameworkRootPath);
        $template->display($html);
    }

    /**
     * public 表示全局，类内部外部子类都可以访问
     * private 表示私有的，只有本类内部可以使用
     * protected 表示受保护的，只有本类或子类或父类中可以访问
     */
}
