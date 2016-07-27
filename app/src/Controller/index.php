<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Check;
use HomoChecker\View\ServerSentEventView;
use HomoChecker\View\JsonView;

require __DIR__ . '/../../../vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'outputBuffering'        => false,
        'addContentLengthHeader' => false,
    ],
]);
$app->get('/check/[{name}/]', function (Request $request, Response $response, array $args) {
    $checker = new Check;
    $name = $args['name'] ?? null;

    switch ($request->getQueryParams()['format'] ?? 'sse') {
        case 'json':
            $view = new JsonView;
            $view->render($checker->execute($name));
            return;

        case 'sse':
            $view = new ServerSentEventView('response');
            $checker->execute($name, [$view, 'render']);
            $view->close();
            return;
    }
});
$app->run();
