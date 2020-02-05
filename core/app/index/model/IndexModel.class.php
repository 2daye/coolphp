<?php

namespace core\app\index\model;

use core\plugin\Model;

class IndexModel
{
    private $db;
    private $table = 'abc_user';

    public function __construct()
    {
        $this->db = Model::getInstance();
        $this->db->table('abc'); //表名
    }

    public function welcome()
    {
        return '欢迎使用 CoolPHP框架 V2.0';
    }

    /**
     * 获取用户列表（带分页）
     * @param $num //第几个
     * @param $number //读取几条
     * @return array|int
     */
    public function userList($num, $number)
    {
        return $this->db->table($this->table)
            ->where(['state' => [0, 1]])
            ->limit($num, $number)
            ->order('id', 'desc')
            ->cache(60)
            ->select();
    }
}