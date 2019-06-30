<?php

namespace core\app\index\model;

use core\plugin\Cache;
use core\tool\Tool;

class IndexModel
{
    private $db;

    //表名
    private $table_name = 'abc';

    public $user_name;
    public $pass_word;

    public function __construct()
    {
        $this->db = \core\plugin\Model::get_instance();
    }

    public function welcome()
    {
        return '欢迎使用 CoolPHP框架 V1.0';
    }

    //获得用户
    public function get_user()
    {
        $cache = new Cache();
        $user = $cache->get('user');
        if ($user) {
            Tool::p('缓存');
            $data = $user;
        } else {
            Tool::p('数据库');
            $data = $this->db->table('ims_wei_lease_user')
                ->where([':id' => [4, 6, 9]])
                ->where([':status' => 1])
                ->select();
            $cache->set('user', $data, 5);
        }
        return $data;
    }
}