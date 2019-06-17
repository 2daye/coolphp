<?php
/**
 * log日志类 - file
 */
namespace core\plugin\Drive\log;

class file
{
    public $path;

    /**
     * 初始化日志
     * 调用配置，判断使用什么日志方法
     * file constructor.
     */
    public function __construct()
    {
        $this->path = ROOT_PATH . \core\plugin\Config::get('log', 'LOG_PATH');
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