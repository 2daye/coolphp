<?php
/**
 * log日志类 - file
 */
namespace core\plugin\Drive\log;

class file
{
    public $path;

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

    /**
     * 初始化日志
     * 调用配置，判断使用什么日志方法
     * file constructor.
     */
    private function __construct()
    {
        $this->path = ROOT_PATH . \core\plugin\Config::get('log', 'LOG_PATH');
    }

    //单例防止克隆
    private function __clone()
    {
    }

    /**
     * 记录日志
     * @param $name //日志内容
     * @param string $logfilename //log文件名
     * @return bool|int
     */
    public function log($name, $logfilename = 'log')
    {
        $log_folder = date(\core\plugin\Config::get('log', 'LOG_FOLDER'));
        if (!is_dir($this->path . $log_folder)) {
            mkdir($this->path . $log_folder, '0777', true);
            chmod($this->path . $log_folder, 0777);
        }
        return file_put_contents($this->path . $log_folder . '/' . $logfilename . '.log', date('Y-m-d H:i:s') . ' - ' . json_encode($name) . PHP_EOL, FILE_APPEND);
    }
}