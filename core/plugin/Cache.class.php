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
    public function set($key, $data, $ttl = null)
    {
        return self::$class->set($key, $data, $ttl);
    }

    /**
     * 获取缓存
     * @param $key //缓存的key
     * @return mixed
     */
    public function get($key)
    {
        return self::$class->get($key);
    }

    /**
     * 删除缓存
     * @param $key //缓存的key
     * @return mixed
     */
    public function delete($key)
    {
        return self::$class->delete($key);
    }

    /**
     * 获取key
     * @param string $key //key名，默认*获取全部的key
     * @return array
     */
    public function get_key($key = '*')
    {
        return self::$class->get_key($key);
    }

    /**
     * 增加缓存的数字
     * @param $key //缓存的key
     * @param null $value //可选，如果填写了，就增加指定数量
     * @return int
     */
    public function increase($key, $value = null)
    {
        return self::$class->increase($key, $value);
    }

    /**
     * 减少缓存的数字
     * @param $key //缓存的key
     * @param null $value //可选，如果填写了，就减少指定数量
     * @return int
     */
    public function reduce($key, $value = null)
    {
        return self::$class->reduce($key, $value);
    }
}
