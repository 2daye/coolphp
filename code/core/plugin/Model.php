<?php

/**
 * 模型父类
 * @author 2daye
 */

namespace core\plugin;

use Medoo\Medoo;

class Model
{
    /**
     * 数据库链接资源
     * @var bool|Medoo
     * @author 2daye
     */
    protected bool|Medoo $database = false;

    /**
     * 链接数据库
     * @throws \Exception
     * @author 2daye
     */
    public function __construct()
    {
        // 判断是否连接数据库
        if ($this->database === false) {
            // 获取数据库配置
            $dbConfig = Config::getAll('database');

            $this->database = new Medoo([
                'type' => 'mysql',
                'host' => $dbConfig['DB_HOST'],
                'database' => $dbConfig['DB_NAME'],
                'username' => $dbConfig['DB_USER'],
                'password' => $dbConfig['DB_PASSWORD'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'port' => $dbConfig['DB_PORT_NUMBER'],
                // 表前缀
                'prefix' => '',
                // 启用日志记录，默认情况下禁用它以获得更好的性能。
                'logging' => true
            ]);
        }
    }
}
