<?php
return array(
    'LOG_DRIVE' => 'file',
    'LOG_PATH' => '/core/log/',
    /*YmdH 表示1个小时分出一个log日志文件将，这样可以避免高并发的时候，出现非常大的log日志*/
    'LOG_FOLDER' => 'YmdH'
);