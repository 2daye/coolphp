<?php
namespace core\plugin\Drive\log;

/*log日志文件驱动方案*/

class file
{
    public $path;

    public function __construct()
    {
        $this->path = ROOT_PATH . \core\plugin\Config::get('log', 'LOG_PATH');
    }

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