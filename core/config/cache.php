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
        'IP' => '127.0.0.1',
        'PORT' => 6379,
        'PASSWORD' => '123456'
    ]
];
