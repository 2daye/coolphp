<?php
return array(
    //CACHE缓存方式 支持file和redis
    'CACHE_WAY' => 'redis',
    'FILE_CACHE' => [
        'PATH' => '/core/cache/file_cache/'
    ],
    'REDIS_CACHE' => [
        'IP' => '127.0.0.1',
        'PORT' => 6379,
        'PASSWORD' => '123456'
    ],
    'MEMCACHED_CACHE' => []
);