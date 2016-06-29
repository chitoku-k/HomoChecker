<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Check;
use HomoChecker\View\ServerSentEventView;

require __DIR__ . '/../../../vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'outputBuffering' => false,
    ],
]);
$app->get('/check[/{name}]', function (Request $request, Response $response, array $args) {
    $view = new ServerSentEventView('response');
    $checker = new Check;
    $checker->execute($args['name'] ?? null, [$view, 'render']);
});
$app->run();
