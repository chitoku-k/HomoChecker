<?php
declare(strict_types=1);

namespace HomoChecker;

use GuzzleHttp\Client;
use HomoChecker\Model\Cache;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\Profile\ProfileProvider;
use HomoChecker\Model\Profile\MastodonProfile;
use HomoChecker\Model\Profile\TwitterProfile;
use HomoChecker\Model\Validator\HeaderValidator;
use HomoChecker\Model\Validator\DOMValidator;
use HomoChecker\Model\Validator\URLValidator;
use HomoChecker\Utilities\Container;
use HomoChecker\Action\CheckAction;
use HomoChecker\Action\ListAction;
use HomoChecker\Action\BadgeAction;
use Interop\Container\ContainerInterface;
use Slim\App;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/config.php';

$container = new Container();
$container['timeout'] = 5;
$container['regex'] = '/https?:\/\/twitter\.com\/mpyw\/?/';
$container['checker'] = function (ContainerInterface $container) {
    return new Check(
        $container['client'],
        $container['homo'],
        $container['profile'],
        ...$container['validators']
    );
};
$container['client'] = function (ContainerInterface $container) {
    return new Client([
        'timeout' => $container['timeout'],
        'allow_redirects' => false,
        'headers' => [
            'User-Agent' => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ],
    ]);
};
$container['homo'] = function (ContainerInterface $container) {
    return new Homo($container['database'], 'users');
};
$container['profile'] = function (ContainerInterface $container) {
    return new ProfileProvider(
        new MastodonProfile($container['client'], $container['cache']),
        new TwitterProfile($container['client'], $container['cache'])
    );
};
$container['cache'] = function (ContainerInterface $container) {
    return new Cache($container['redis']);
};
$container['redis'] = function (ContainerInterface $container) {
    $redis = new \Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT);
    return $redis;
};
$container['validators'] = function (ContainerInterface $container) {
    return [
        new HeaderValidator($container['regex']),
        new DOMValidator($container['regex']),
        new URLValidator($container['regex']),
    ];
};
$container['database'] = function (ContainerInterface $container) {
    return new \PDO(DB_DSN, DB_USER, DB_PASS, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ]);
};

$app = new App($container);
$app->get('/check/[{name}/]', CheckAction::class);
$app->get('/list/[{name}/]', ListAction::class);
$app->get('/badge/[{status}/]', BadgeAction::class);
$app->run();
