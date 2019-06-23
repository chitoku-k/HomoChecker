<?php
declare(strict_types=1);

const DB_HOST = 'database';
const DB_PORT = 3306;
const DB_DATABASE = 'homo';
const DB_USER = 'homo';
const DB_PASS = 'homo';

const REDIS_HOST = 'redis';
const REDIS_PORT = 6379;

return [
    'cache.default' => 'default',
    'cache.stores.default' => [
        'driver' => 'redis',
    ],
    'database.default' => 'default',
    'database.connections' => [
        'default' => [
            'driver' => 'mysql',
            'host' => DB_HOST,
            'port' => DB_PORT,
            'database' => DB_DATABASE,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => 'utf8',
        ],
    ],
    'database.redis' => [
        'default' => [
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ],
    ],
    'client' => [
        'timeout' => 5,
        'allow_redirects' => false,
        'headers' => [
            'User-Agent' => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ],
    ],
    'addContentLengthHeader' => false,
    'displayErrorDetails' => false,
    'determineRouteBeforeAppMiddleware' => false,
    'httpVersion' => '1.1',
    'outputBuffering' => false,
    'responseChunkSize' => 4096,
    'routerCacheFile' => false,
    'regex' => '/https?:\/\/twitter\.com\/mpyw\/?/',
];
