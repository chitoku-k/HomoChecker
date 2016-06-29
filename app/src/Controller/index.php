<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Check;
use HomoChecker\View\JsonView;

require __DIR__ . '/../../../vendor/autoload.php';

$app = new \Slim\App;
$app->get('/check[/{name}]', function (Request $request, Response $response, array $args) {
    $checker = new Check;
    $checker->execute([new JsonView, 'render']);
});
$app->run();
