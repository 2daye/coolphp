<?php

namespace core\app\index\model;

class IndexModel
{
    private $db;

    //表名
    private $table_name = 'abc';

    private $user_name;
    private $pass_word;

    public function __construct()
    {
        $this->db = \core\plugin\Model::get_instance();
    }

    /*__set()方法用于设置私有属性值
    在直接对类设置私有属性值的时候，自动调用了这个__set()方法，为私有属性赋值*/
    public function __set($_key, $_value)
    {
        $this->$_key = $_value;
    }

    /*__get()方法用于获取私有属性值
    在直接获取私有属性值的时候，自动调用了这个 __get()方法，获取私有属性的值*/
    public function __get($_key)
    {
        return $this->$_key;
    }

    public function welcome()
    {
        return '欢迎使用 CoolPHP框架 V1.0';
    }

    //获得用户
    public function get_user()
    {
        return $this->db->select($this->table_name, '*');
    }
}