<?php
// 控制器命名空间
namespace core\app\index\controller;

// 父类控制器
use core\plugin\Controller;

class Index extends Controller
{
    public function main()
    {
        parent::assign('smile', ':&nbsp;)');
        parent::assign('welcome', '欢迎使用 CoolPhp框架 V3.0');
        parent::display('index/view/index.html');
    }
}
