<?php
declare(strict_types=1);

use HomoChecker\Logging\CustomizeFormatter;

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
    'logging.default' => 'default',
    'logging.channels.default' => [
        'driver' => 'single',
        'tap' => [CustomizeFormatter::class . ":[%datetime%] %level_name%: %message% %context% %extra%\n"],
        'path' => 'php://stderr',
        'level' => 'info',
    ],
    'logging.channels.router' => [
        'driver' => 'single',
        'tap' => [CustomizeFormatter::class . ":%message% %context% %extra%\n"],
        'path' => 'php://stderr',
        'level' => 'info',
    ],
    'logging.channels.emergency' => [
        'path' => 'php://stderr',
    ],
    'client' => [
        'timeout' => 5,
        'allow_redirects' => false,
        'headers' => [
            'User-Agent' => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ],
    ],
    'twitter.client' => [
        'base_uri' => 'https://api.twitter.com/1.1/',
        'auth' => 'oauth',
    ],
    'twitter.oauth' => [
        'consumer_key' => env('HOMOCHECKER_TWITTER_CONSUMER_KEY'),
        'consumer_secret' => env('HOMOCHECKER_TWITTER_CONSUMER_SECRET'),
        'token' => env('HOMOCHECKER_TWITTER_TOKEN'),
        'token_secret' => env('HOMOCHECKER_TWITTER_TOKEN_SECRET'),
    ],
    'httpVersion' => '1.1',
    'responseChunkSize' => 4096,
    'routerCacheFile' => false,
    'regex' => '/https?:\/\/twitter\.com\/mpyw\/?/',
];
