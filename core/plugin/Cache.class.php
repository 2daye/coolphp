<?php
/**
 * 缓存类
 * 作者：2daye
 */
namespace core\plugin;

class Cache
{
    private static $class;

    /**
     * 缓存初始化
     * 获取驱动，判断调用那种缓存
     * Cache constructor.
     * @param null $way //使用那种缓存，默认调用配置的
     */
    public function __construct($way = null)
    {
        //判断是否有缓存方式存入，没有就用配置的方法
        $drive = $way != null ? $way : \core\plugin\Config::get('cache', 'CACHE_WAY');
        //call_user_func() 方法可以把字符串当方法调用
        self::$class = call_user_func('\core\plugin\Drive\cache\\' . $drive . '::get_instance');
    }

    /**
     * 设置缓存
     * @param $key //缓存的key
     * @param $data //缓存的数据
     * @param $ttl //缓存过期时间，默认无过期时间
     * @return mixed
     */
    public static function set($key, $data, $ttl = null)
    {
        return self::$class->set($key, $data, $ttl);
    }

    /**
     * 获取缓存
     * @param $key //缓存的key
     * @return mixed
     */
    public static function get($key)
    {
        return self::$class->get($key);
    }

    /**
     * 删除缓存
     * @param $key //缓存的key
     * @return mixed
     */
    public static function delete($key)
    {
        return self::$class->delete($key);
    }
}
