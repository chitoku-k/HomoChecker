<?php
namespace HomoChecker;

use HomoChecker\Utilities\Container;
use HomoChecker\Action\CheckAction;
use HomoChecker\Action\ListAction;
use HomoChecker\Action\BadgeAction;
use Slim\App;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/config.php';

$app = new App(new Container);
$app->get('/check/[{name}/]', CheckAction::class);
$app->get('/list/[{name}/]', ListAction::class);
$app->get('/badge/[{status}/]', BadgeAction::class);
$app->run();
