<?php
namespace HomoChecker;

require __DIR__ . '/../../vendor/autoload.php';

$app = new \Slim\App(new Container);
$app->get('/check/[{name}/]', 'HomoChecker\Action\CheckAction');
$app->get('/list/[{name}/]', 'HomoChecker\Action\ListAction');
$app->get('/badge/[{status}/]', 'HomoChecker\Action\BadgeAction');
$app->run();
