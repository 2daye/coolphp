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
     * @return redis
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

    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        //不能设置key为*的缓存
        if ('*' === $key) {
            return false;
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }
        if (null == $ttl) {
            return $this->redis->set($key, $value);
        } else {
            return $this->redis->setex($key, $ttl, $value);
        }
    }

    /**
     * 获取缓存
     * @param $key
     * @return bool|mixed|string
     */
    public function get($key)
    {
        $result = $this->redis->get($key);
        if (false !== $result) {
            json_decode($result);
            if (JSON_ERROR_NONE === json_last_error()) {
                $array = json_decode($result, true);
                if (is_array($array)) {
                    return $array;
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @param $key //要删除的缓存key，如果是*，就删除全部的缓存
     * @return bool
     */
    public function delete($key)
    {
        if ('*' === $key) {
            $key_array = $this->get_key();
            foreach ($key_array as $key => $value) {
                $this->redis->delete($value);
            }
        } else {
            $this->redis->delete($key);
        }
        return true;
    }

    /**
     * 获取key
     * @param string $key //key名，默认*获取全部的key
     * @return array
     */
    public function get_key($key = '*')
    {
        return $this->redis->keys($key);
    }

    /**
     * 增加缓存的数字
     * @param $key //缓存的key
     * @param null $value //可选，如果填写了，就增加指定数量
     * @return int
     */
    public function increase($key, $value = null)
    {
        if (null === $value) {
            return $this->redis->incr($key);
        } else {
            return $this->redis->incrBy($key, $value);
        }
    }

    /**
     * 减少缓存的数字
     * @param $key //缓存的key
     * @param null $value //可选，如果填写了，就减少指定数量
     * @return int
     */
    public function reduce($key, $value = null)
    {
        if (null === $value) {
            return $this->redis->decr($key);
        } else {
            return $this->redis->decrBy($key, $value);
        }
    }

    //单例防止克隆
    private function __clone()
    {
    }

    /**
     * 析构函数，关闭与redis的连接
     */
    public function __destruct()
    {
        $this->redis->close();
    }
}