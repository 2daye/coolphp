<?php
/**
 * 文件缓存类
 * 在set方法中，我们使用了，file()函数来打开文件，在get方法中，
 * 跟换了，file_get_contents函数来打开缓存文件
 * 这是因为，file_get_contents函数要快于前者
 *
 * 一般，我们将复杂或者数据量多而没有必要分开存储的数据封装成一个多维数组通过 serialize()
 * 转成字符串，然后存进数据库，需要的时候再拿出来转成数组再用，
 * 而拿出了转成数组用的就是php的 unserialize()
 *
 * 作者：2daye
 */
namespace core\plugin\Drive\cache;

class file
{
    //定义$instance用于存放实例化的对象
    private static $instance;

    //静态单例模式
    public static function getInstance()
    {
        /**
         * 通过使用 instanceof操作符 和 self关键字 ，
         * 可以检测到类是否已经被实例化，如果 $instance 没有保存，类本身的实例。
         */
        if (!(self::$instance instanceof self)) {
            //就把本身的实例赋给 $instance
            self::$instance = new self();
        }
        return self::$instance;
    }

    //私有构造函数，实现单例
    private function __construct()
    {
    }

    //单例防止克隆
    private function __clone()
    {
    }

    /**
     * 设置写入缓存
     * @param $key //缓存的key
     * @param $data //要缓存的数据
     * @param null $ttl //缓存过期时间
     * @return bool
     * @throws \Exception
     */
    public function set($key, $data, $ttl = null)
    {
        /**
         * 打开文件为 读/写 模式
         * (fopen() 函数打开文件/"a+" 读写方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之)
         */
        $h = fopen($this->getFilename($key), 'a+');
        if (!$h) {
            throw new \Exception('无法写入缓存');
        }
        //写锁定，在完成之前文件关闭不可再写入
        flock($h, LOCK_EX);

        //到该文件头
        fseek($h, 0);

        //清空文件内容
        ftruncate($h, 0);

        /**
         * 根据生命周期 $ttl 写入 到期时间
         * serialize() - 产生一个可存储的值的表示
         * 使用 serialize() 函数将这个数组转化为一个序列化的字符串,到时候使用unserialize()在解析出来
         */
        $ttl = $ttl == null ? (((60 * 60) * 24) * 365) * 666 : $ttl;
        $data = serialize(array(time() + $ttl, $data));

        //数据压缩
        //$data = gzcompress($data, 3);


        //fwrite()写入文件,缓存文件
        if (fwrite($h, $data) === false) {
            throw new \Exception('无法写入缓存');
        }
        //关闭缓存文件
        fclose($h);

        clearstatcache();

        return true;
    }

    /**
     * 获取缓存数据
     * 如果未取出返回失败信息
     * @param $key //缓存的key
     * @return bool
     */
    public function get($key)
    {
        $fileName = $this->getFilename($key);
        //file_exists()函数检查文件或目录是否存在
        if (!file_exists($fileName)) {
            return false;
        }
        //fopen()函数打开缓存文件，"r"	只读方式打开，将文件指针指向文件头
        $h = fopen($fileName, 'r');
        //判断打开缓存文件是否成功
        if (!$h) {
            return false;
        }
        //文件读写锁定
        flock($h, LOCK_SH);
        //file_get_contents() 函数把整个缓存文件读入一个字符串中
        $data = file_get_contents($fileName);
        //关闭缓存文件
        fclose($h);

        //$data = gzuncompress($data);

        //使用unserialize()函数把之前 使用serialize()函数存入的字符串，变成数组
        $data = @unserialize($data);
        if (!$data) {
            //如果反序化失败，则彻底删除该文件
            unlink($fileName);
            return false;
        }

        //判断缓存文件是否过期了
        if (time() > $data[0]) {
            //如果缓存已经过期，删除该文件
            unlink($fileName);
            return false;
        }

        //抛出缓存数据
        return $data[1];
    }

    /**
     * 删除缓存
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $fileName = $this->getFilename($key);
        //file_exists() 函数检查文件或目录是否存在
        if (file_exists($fileName)) {
            //删除文件
            return unlink($fileName);
        } else {
            return false;
        }
    }

    /**
     * 获取缓存文件名
     * 不存在就创建
     * @param $key
     * @return string
     * @throws \Exception
     */
    private function getFilename($key)
    {
        /**
         * 也可以把缓存存在session文件夹下，也可以自定义到项目文件夹
         * return ini_get('session.save_path') . '/s_cache' . md5($key);
         */
        //获取配置，文件缓存路径
        $fileCachePath = ROOT_PATH . \core\plugin\Config::get('cache', 'FILE_CACHE')['PATH'];
        if (!file_exists($fileCachePath)) {
            $createFileCachePath = mkdir($fileCachePath, 0777, true);
            if (!$createFileCachePath) {
                throw new \Exception('无法创建缓存路径');
            }
        }
        return $fileCachePath . md5($key);
    }
}
