<?php

namespace core\plugin;

class Code
{
    // code
    const OK = 2000;
    const BAD_REQUEST = 4000;
    const UNAUTHORIZED = 4001;
    const FORBIDDEN = 4003;
    const NOT_FOUND = 4004;
    const EXISTED = 4005;
    const INTERNAL_SERVER_ERROR = 5000;

    // message
    const MESSAGE = [
        self::OK => '成功',
        self::BAD_REQUEST => '请求参数错误',
        self::UNAUTHORIZED => '请登录验证身份',
        self::FORBIDDEN => '拒绝请求',
        self::NOT_FOUND => '资源不存在',
        self::EXISTED => '资源已存在',
        self::INTERNAL_SERVER_ERROR => '服务器异常'
    ];
}
