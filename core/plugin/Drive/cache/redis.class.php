<?php
/**
 * 缓存类 - redis
 */
namespace core\plugin\Drive\cache;

class redis
{
    //$redis 存放数据库连接句柄
    private $redis = null;
    //$instance 用于存放实例化的对象
    private static $instance;

    /**
     * 静态单例模式
     * @return Model
     */
    public static function get_instance()
    {
        /**
         * 通过使用 instanceof操作符 和 self关键字 ，
         * 可以检测到类是否已经被实例化，
         * 如果 $instance 没有保存，类本身的实例。
         */
        if (!(self::$instance instanceof self)) {
            //就把本身的实例赋给 $instance
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 私有构造函数，实现单例
     * @throws \Exception
     */
    private function __construct()
    {
        if (null === $this->redis) {
            //载入配置redis配置文件
            $redisconfig = \core\plugin\Config::get('cache', 'REDIS_CACHE');
            //连接redis
            $this->redis = new \redis();
            $this->redis->connect($redisconfig['IP'], $redisconfig['PORT']);
            if (!$this->redis->auth($redisconfig['PASSWORD']) || !$this->redis->ping() == '+PONG') {
                if (DEBUG) {
                    throw new \Exception('Redis链接失败！');
                }
            }
        }
    }

    //单例防止克隆
    private function __clone()
    {
    }

    public function set($key, $value, $ttl = null)
    {
        if (null == $ttl) {
            return $this->redis->set($key, $value);
        } else {
            return $this->redis->setex($key, $ttl, $value);
        }
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function delete($key)
    {
        $this->redis->delete($key);
        return true;
    }
}