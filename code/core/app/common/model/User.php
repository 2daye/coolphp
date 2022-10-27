<?php

namespace core\app\common\model;

use core\plugin\Code;
use core\plugin\Model;
use core\plugin\RESTful;

class User extends Model
{
    // 表名
    protected string $table = 'user';

    /**
     * 注册
     * @param array $data
     * @return array
     * @author 2daye
     */
    public function register(array $data): array
    {
        $count = $this->database->count($this->table, ['phone' => $data['phone']]);

        // 如果用户存在就拒绝注册
        if ($count > 0) {
            return (new RESTful())->response(Code::FORBIDDEN, '手机号已经存在')->array();
        }

        $result = $this->database->insert($this->table, $data);

        // 判断用户是否新增成功
        if ($result->rowCount() > 0) {
            return (new RESTful())->response(Code::OK)->array();
        }

        return (new RESTful())->response(Code::INTERNAL_SERVER_ERROR)->array();
    }
}