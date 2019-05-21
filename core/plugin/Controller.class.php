<?php
/*
 * 控制器父类
 * 1.初始化模板引擎
 * 2.实例化 分页类
 * */
namespace core\plugin;

class Controller
{
    /*
     * public    表示全局，类内部外部子类都可以访问
     * private   表示私有的，只有本类内部可以使用
     * protected 表示受保护的，只有本类或子类或父类中可以访问
     * */

    //定义模板引擎初始为空。
    private static $Template = null;

    //控制器父类的构造方法
    /*public function __construct(){}*/

    //初始化模板类
    protected function init_template()
    {
        //调用模板引擎
        self::$Template = new \core\plugin\Template();
        //调用模板引擎父类Smarty的assign方法，设置网页模板根目录
        self::$Template->assign('WebSite', \core\tool\Tool::getUrl());
    }

    //重写Smarty的assign方法
    protected function assign($variable, $value)
    {
        //判断是否初始化过模板引擎
        if (self::$Template == null) {
            $this->init_template();
        }
        self::$Template->assign($variable, $value);
    }

    //重写Smarty的display方法
    protected function display($value)
    {
        //判断是否初始化过模板引擎
        if (self::$Template == null) {
            $this->init_template();
        }
        self::$Template->display($value);
    }

    //检验用户是否登录
    protected function check_logon()
    {
        $UserInfo = \core\tool\Tool::session('get', 'user');
        if ($UserInfo != '' && $UserInfo !== false) {
            return true;
        } else {
            return false;
        }
    }

    //设置分页
    protected function page($total, $listRows)
    {
        if (isset($_GET['page'])) {
            \core\tool\Tool::session('set', 'page', \core\tool\Tool::get('page', 'int', 1));
        } else {
            \core\tool\Tool::session('set', 'page', 1);
        }
        $page = new \core\plugin\Page($total, $listRows);
        $this->assign("num", $page->listRowsBegin());
        $this->assign("page", $page->display([0, 1, 2, 3, 4, 6]));
        return $page->limit;
    }

    //判断用户是否有权限操作后台
    protected function decide_role_backstage()
    {
        //判断该角色是否可以进入后台
        $user_role = \core\tool\Tool::session('get', 'user')['user_role'];
        $BACKSTAGE_ROLE = \core\plugin\Config::get('system', 'BACKSTAGE_ROLE');
        if (in_array($user_role, $BACKSTAGE_ROLE)) {
            return true;
        } else {
            return false;
        }
    }

    //后台左侧菜单
    protected function get_backstage_menu()
    {
        //获得角色id
        $user_role = \core\tool\Tool::session('get', 'user')['user_role'];
        //new出角色菜单模块
        $role_menu = new \core\app\admin\model\RoleMenuModel();
        //传入角色id
        $role_menu->role_id = $user_role;
        //获得用户角色菜单
        $user_role_menu = $role_menu->get_role_menu();
        //new Tree模块
        $tree = new \core\plugin\Tree();
        //得到树数组
        $menu_tree_array = $tree->get_tree_array($user_role_menu, 0);
        //得到ul li 菜单结构
        $backstage_menu = $tree->get_backstage_menu($menu_tree_array, \core\tool\Tool::getUrl());
        //输出到后台模板
        $this->assign('backstage_menu', $backstage_menu);
    }
}