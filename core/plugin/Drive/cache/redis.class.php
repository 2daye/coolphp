<?php
/**
 * 缓存类 - redis
 * 作者：2daye
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
    public static function getInstance()
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
     * 单例防止克隆
     */
    private function __clone()
    {
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
    public function getKey($key = '*')
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

    /**
     * 插入数据到列表缓存
     * @param $key //列表名
     * @param $value //要插入的数据
     * @param string $type //从列表的那一侧插入，默认左侧
     * @return bool|mixed
     */
    public function insertList($key, $value, $type = 'l')
    {
        $arr = [];
        //先把$key存入数组第一位
        $arr[] = $key;
        //判断用什么方法插入列表
        $func = $type === 'l' ? 'lPush' : 'rPush';
        //是否插入多个数据
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $v = is_array($v) ? json_encode($v) : $v;
                $arr[] = $v;
            }
        } else {
            $arr[] = $value;
        }
        //利用call_user_func_array函数调用redis的方法插入数据
        return call_user_func_array([$this->redis, $func], $arr);
    }

    /**
     * 获取列表长度
     * @param $key //列表名
     * @return int
     */
    public function getListLength($key)
    {
        return $this->redis->lLen($key);
    }

    /**
     * 获取列表数据
     * @param $key
     * @param int $s
     * @param int $e
     * @return array
     */
    public function getList($key, $s = 0, $e = -1)
    {
        return $this->redis->lRange($key, $s, $e);
    }

    /**
     * 设置哈希缓存
     * @param $hash
     * @param $key
     * @param $value
     * @return int
     */
    public function setHash($hash, $key, $value = null)
    {
        //判断key是否是数组，如果是数组就调用批量添加元素方法
        if (is_array($key)) {
            return $this->redis->hMset($hash, $key);
        } else {
            //调用单个元素添加
            if (null !== $value) {
                return $this->redis->hSet($hash, $key, $value);
            } else {
                return false;
            }
        }
    }

    /**
     * 获取哈希
     * @param $hash
     * @param $key
     * @return string
     */
    public function getHash($hash, $key)
    {
        //判断如果key是数组，就调用获取多个value
        if (is_array($key)) {
            return $this->redis->hMGet($hash, $key);
        } else {
            return $this->redis->hGet($hash, $key);
        }
    }

    /**
     * 获取哈希长度
     * @param $hash
     * @return int
     */
    public function getHashLength($hash)
    {
        return $this->redis->hLen($hash);
    }

    /**
     * 删除哈希中的key
     * @param $hash
     * @param $key
     * @return mixed
     */
    public function delectHashKey($hash, $key)
    {
        $arr = [];
        $arr[] = $hash;
        if (!is_array($key)) {
            $arr[] = $hash;
            $arr[] = $key;
        } else {
            array_unshift($key, $hash);
            $arr = $key;
        }
        return call_user_func_array([$this->redis, 'hDel'], $arr);
    }

    /**
     * 获取哈希全部的键
     * @param $hash
     * @return array
     */
    public function getHashAllKey($hash)
    {
        return $this->redis->hKeys($hash);
    }

    /**
     * 获取哈希全部的值
     * @param $hash
     * @return array
     */
    public function getHashAllValue($hash)
    {
        return $this->redis->hVals($hash);
    }

    /**
     * 获取哈希全部的键和值
     * @param $hash
     * @return array
     */
    public function getHashAll($hash)
    {
        return $this->redis->hGetAll($hash);
    }

    /**
     * 判断哈希是否存在键域
     * @param $hash
     * @param $key
     * @return bool
     */
    public function hashExistKey($hash, $key)
    {
        return $this->redis->hExists($hash, $key);
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
        return $this->redis->hIncrBy($hash, $key, $number);
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
        $arr = [];
        if (!is_array($value)) {
            $arr[] = $key;
            $arr[] = $value;
        } else {
            array_unshift($value, $key);
            $arr = $value;
        }
        return call_user_func_array([$this->redis, 'sAdd'], $arr);
    }

    /**
     * 获取无序集合
     * @param $key
     * @return array
     */
    public function getGroup($key)
    {
        return $this->redis->sMembers($key);
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
        return $this->redis->zAdd($key, $score, $value);
    }

    /**
     * 获取有序集合
     * @param $key //集合键
     * @param int $start //开始读取位置
     * @param int $end //结束读取位置，-1 = 最后一位
     * @param string $sorting //升序/降序，默认
     * @param bool $withscores //是否输出分数，默认不输出
     * @return array
     */
    public function getOrderlyGroup($key, $start = 0, $end = -1, $sorting = 'desc', $withscores = false)
    {
        if ('desc' === $sorting) {
            return $this->redis->zRevRange($key, $start, $end, $withscores);
        } else {
            return $this->redis->zRange($key, $start, $end, $withscores);
        }
    }

    /**
     * 监视key
     * @param string|array $key //要监视的key，传入字符串或数组
     * @return bool
     */
    public function monitorKey($key)
    {
        $this->redis->watch($key);
        return true;
    }

    /**
     * 开启事务
     * @return \Redis
     */
    public function openTransaction()
    {
        return $this->redis->multi();
    }

    /**
     * 提交事务
     * 事务提交成功返回数组失败返回false
     * @return array|bool
     */
    public function commitTransaction()
    {
        return $this->redis->exec();
    }

    /**
     * 关闭事务
     */
    public function closeTransaction()
    {
        $this->redis->discard();
    }

    /**
     * 析构函数，关闭与redis的连接
     */
    public function __destruct()
    {
        $this->redis->close();
    }
}
