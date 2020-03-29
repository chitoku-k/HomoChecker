<?php
declare(strict_types=1);

return [
    'cache.default' => 'default',
    'cache.stores.default' => [
        'driver' => 'redis',
    ],
    'database.default' => 'default',
    'database.connections' => [
        'default' => [
            'driver' => 'mysql',
            'host' => env('HOMOCHECKER_DB_HOST'),
            'port' => env('HOMOCHECKER_DB_PORT', 3306),
            'database' => env('HOMOCHECKER_DB_DATABASE', 'homo'),
            'username' => env('HOMOCHECKER_DB_USERNAME'),
            'password' => env('HOMOCHECKER_DB_PASSWORD'),
            'charset' => 'utf8',
        ],
    ],
    'database.redis' => [
        'default' => [
            'host' => env('HOMOCHECKER_REDIS_HOST'),
            'port' => (int) env('HOMOCHECKER_REDIS_PORT', 6379),
        ],
    ],
    'client' => [
        'timeout' => 5,
        'allow_redirects' => false,
        'headers' => [
            'User-Agent' => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ],
    ],
    'httpVersion' => '1.1',
    'responseChunkSize' => 4096,
    'routerCacheFile' => false,
    'regex' => '/https?:\/\/twitter\.com\/mpyw\/?/',
];
