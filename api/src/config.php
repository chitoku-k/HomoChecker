<?php
declare(strict_types=1);

use HomoChecker\Logging\CustomizeFormatter;

return [
    'database.default' => 'default',
    'database.connections' => [
        'default' => [
            'driver' => 'pgsql',
            'host' => env('HOMOCHECKER_DB_HOST'),
            'port' => env('HOMOCHECKER_DB_PORT', 5432),
            'database' => env('HOMOCHECKER_DB_DATABASE', 'homo'),
            'username' => env('HOMOCHECKER_DB_USERNAME'),
            'password' => env('HOMOCHECKER_DB_PASSWORD'),
            'sslmode' => env('HOMOCHECKER_DB_SSLMODE', 'prefer'),
            'sslcert' => env('HOMOCHECKER_DB_SSLCERT'),
            'sslkey' => env('HOMOCHECKER_DB_SSLKEY'),
            'sslrootcert' => env('HOMOCHECKER_DB_SSLROOTCERT'),
            'charset' => 'utf8',
        ],
    ],
    'logging.default' => 'default',
    'logging.channels.default' => [
        'driver' => 'single',
        'tap' => [CustomizeFormatter::class . ":[%datetime%] %level_name%: %message% %context% %extra%\n"],
        'path' => 'php://stderr',
        'level' => env('HOMOCHECKER_LOG_LEVEL', 'info'),
    ],
    'logging.channels.router' => [
        'driver' => 'single',
        'tap' => [CustomizeFormatter::class . ":%message% %context% %extra%\n"],
        'path' => 'php://stderr',
        'level' => env('HOMOCHECKER_LOG_LEVEL', 'info'),
    ],
    'logging.channels.emergency' => [
        'path' => 'php://stderr',
    ],
    'logging.skipPaths' => [
        '/healthz',
        '/metrics',
    ],
    'activityPub.actor' => [
        'id' => env('HOMOCHECKER_AP_ACTOR_ID'),
        'public_key' => env('HOMOCHECKER_AP_ACTOR_PUBLIC_KEY'),
    ],
    'client' => [
        'timeout' => 5,
        'allow_redirects' => false,
        'curl' => [
            CURLOPT_CERTINFO => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_3,
        ],
        'headers' => [
            'User-Agent' => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ],
        'http_errors' => false,
    ],
    'client.redirect' => 5,
    'mastodon.client' => [
        'timeout' => 5,
    ],
    'twitter.client' => [
        'timeout' => 5,
        'base_uri' => 'https://api.twitter.com/1.1/',
        'auth' => 'oauth',
    ],
    'twitter.oauth' => [
        'consumer_key' => env('HOMOCHECKER_TWITTER_CONSUMER_KEY'),
        'consumer_secret' => env('HOMOCHECKER_TWITTER_CONSUMER_SECRET'),
        'token' => env('HOMOCHECKER_TWITTER_TOKEN'),
        'token_secret' => env('HOMOCHECKER_TWITTER_TOKEN_SECRET'),
    ],
    'regex' => '/https?:\/\/twitter\.com\/mpyw\/?/',
];
