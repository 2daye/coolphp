<?php

//控制器命名空间
namespace core\app\index\controller;

//父类控制器
use core\plugin\Controller;
//模型
use core\app\index\model\IndexModel;
//框架工具
use core\tool\Tool;

class IndexController extends Controller
{
    public function main()
    {
        //使用index模型
        /*$IndexModel = new IndexModel();
        $welcome = $IndexModel->welcome();*/
        parent::assign('smile', ':&nbsp;)');
        parent::assign('welcome', '欢迎使用 CoolPhp框架 V1.1.0');
        parent::display('index/view/index.html');
    }
}