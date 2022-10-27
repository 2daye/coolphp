<?php
/**
 * 框架缓存配置文件
 * 支持File和Redis
 */
return [
    'CACHE_WAY' => 'file',
    'FILE_CACHE' => [
        'PATH' => '/core/cache/file_cache/'
    ],
    'REDIS_CACHE' => [
        'IP' => 'localhost',
        'PORT' => 6379,
        'PASSWORD' => '123456'
    ]
];
