<?php
/**
 * 框架日志配置文件
 * 'LOG_FOLDER' => 'YmdH'
 * YmdH 表示1个小时分出一个log日志文件将，
 * 这样可以避免高并发的时候，出现非常大的log日志。
 */
return [
    'LOG_DRIVE' => 'file',
    'LOG_PATH' => '/core/log/',
    'LOG_FOLDER' => 'YmdH'
];
