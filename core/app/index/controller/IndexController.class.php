<?php
//控制器命名空间
namespace core\app\index\controller;

//父类控制器
use core\tool\Tool;
use core\plugin\Controller;

class IndexController extends Controller
{
    public function main()
    {
        parent::assign('smile', ':&nbsp;)');
        parent::assign('welcome', '欢迎使用 CoolPhp框架 V1.0');
        parent::display('index/view/index.html');
    }
}
