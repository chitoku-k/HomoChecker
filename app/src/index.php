<?php
namespace HomoChecker;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\Proxy;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\Icon;
use HomoChecker\Model\Validator\HeaderValidator;
use HomoChecker\Model\Validator\DOMValidator;
use HomoChecker\Model\Validator\URLValidator;
use HomoChecker\Utilities\RawCurlFactory;
use HomoChecker\Utilities\Container;
use HomoChecker\Action\CheckAction;
use HomoChecker\Action\ListAction;
use HomoChecker\Action\BadgeAction;
use Interop\Container\ContainerInterface;
use Slim\App;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/config.php';

$container = new Container;
$container['timeout'] = 5;
$container['regex'] = '/https?:\/\/twitter\.com\/mpyw\/?/';
$container['checker'] = function (ContainerInterface $container) {
    return new Check(
        $container['client'],
        $container['homo'],
        $container['icon'],
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
        'handler' => HandlerStack::create(Proxy::wrapSync(
            new CurlMultiHandler([
                'handle_factory' => new RawCurlFactory(50),
            ]),
            new CurlHandler()
        )),
    ]);
};
$container['homo'] = function (ContainerInterface $container) {
    return new Homo($container['database'], 'users');
};
$container['icon'] = function (ContainerInterface $container) {
    return new Icon($container['client']);
};
$container['validators'] = function (ContainerInterface $container) {
    return [
        new HeaderValidator($container['regex']),
        new DOMValidator($container['regex']),
        new URLValidator($container['regex']),
    ];
};
$container['database'] = function (ContainerInterface $container) {
    return new \PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASS, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ]);
};

$app = new App($container);
$app->get('/check/[{name}/]', CheckAction::class);
$app->get('/list/[{name}/]', ListAction::class);
$app->get('/badge/[{status}/]', BadgeAction::class);
$app->run();
