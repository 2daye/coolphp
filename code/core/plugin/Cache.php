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
        self::$class = call_user_func('\core\plugin\Drive\cache\\' . $drive . '::getInstance');
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
    public function getKey($key = '*')
    {
        return self::$class->getKey($key);
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

    /**
     * 插入数据到列表
     * @param $key //列表名
     * @param $value //要插入的数据
     * @param string $type //从列表的那一侧插入，默认左侧
     * @return bool|mixed
     */
    public function insertList($key, $value, $type = 'l')
    {
        return self::$class->insertList($key, $value, $type);
    }

    /**
     * 获取列表长度
     * @param $key //列表名
     * @return int
     */
    public function getListLength($key)
    {
        return self::$class->getListLength($key);
    }

    /**
     * 设置哈希缓存
     * @param $hash
     * @param $key
     * @param $value
     * @return int
     */
    public function setHash($hash, $key, $value)
    {
        return self::$class->setHash($hash, $key, $value);
    }

    /**
     * 获取哈希
     * @param $hash
     * @param $key
     * @return string
     */
    public function getHash($hash, $key)
    {
        return self::$class->getHash($hash, $key);
    }

    /**
     * 获取哈希长度
     * @param $hash
     * @return int
     */
    public function getHashLength($hash)
    {
        return self::$class->getHashLength($hash);
    }

    /**
     * 删除哈希中的key
     * @param $hash
     * @param $key
     * @return mixed
     */
    public function delecHashKey($hash, $key)
    {
        return self::$class->delecHashKey($hash, $key);
    }

    /**
     * 获取哈希全部的键
     * @param $hash
     * @return array
     */
    public function getHashAllKey($hash)
    {
        return self::$class->getHashAllKey($hash);
    }

    /**
     * 获取哈希全部的值
     * @param $hash
     * @return array
     */
    public function getHashAllValue($hash)
    {
        return self::$class->getHashAllValue($hash);
    }

    /**
     * 获取哈希全部的键和值
     * @param $hash
     * @return array
     */
    public function getHashAll($hash)
    {
        return self::$class->getHashAll($hash);
    }

    /**
     * 判断哈希是否存在键域
     * @param $hash
     * @param $key
     * @return bool
     */
    public function hashExistKey($hash, $key)
    {
        return self::$class->hashExistKey($hash, $key);
    }

    /**
     * 增加哈希的值
     * @param $hash
     * @param $key
     * @param $number
     * @return int
     */
    public function increaseHashValue($hash, $key, $number)
    {
        return self::$class->increaseHashValue($hash, $key, $number);
    }

    /**
     * 插入无序集合
     * 无序集合，每次插入都可能会弄乱排序
     * @param $key
     * @param $value
     * @return mixed
     */
    public function insertGroup($key, $value)
    {
        return self::$class->insertGroup($key, $value);
    }

    /**
     * 获取无序集合
     * @param $key
     * @return array
     */
    public function getGroup($key)
    {
        return self::$class->getGroup($key);
    }

    /**
     * 插入有序集合
     * 有序集合，每次插入都根据score分数，进行排序
     * @param $key
     * @param $score
     * @param $value
     * @return int
     */
    public function insertOrderlyGroup($key, $score, $value)
    {
        return self::$class->insertOrderlyGroup($key, $score, $value);
    }

    /**
     * 开启事务
     * @return \Redis
     */
    public function openTransaction()
    {
        return self::$class->openTransaction();
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commitTransaction()
    {
        return self::$class->commitTransaction();
    }

    /**
     * 监视key
     * @param $key
     */
    public function monitorKey($key)
    {
        return self::$class->monitorKey($key);
    }

    public function getList($key, $s = 0, $e = -1)
    {
        return self::$class->getList($key, $s, $e);
    }
}
