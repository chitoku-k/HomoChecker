<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\HomoResponse;
use HomoChecker\View\ServerSentEventView;
use HomoChecker\View\JsonView;

require __DIR__ . '/../../../vendor/autoload.php';

$app = new \Slim\App([
    'notFoundHandler' => function ($c) {
        return function (Request $request, Response $response) use ($c) {
            return $c['response']->withStatus(404)->withJson([
                'errors' => [
                    [
                        'message' => 'Page not found',
                    ],
                ],
            ]);
        };
    },
], [
    'settings' => [
        'outputBuffering'        => false,
        'addContentLengthHeader' => false,
    ],
]);
$app->get('/check/[{name}/]', function (Request $request, Response $response, array $args) {
    $name = $args['name'] ?? null;

    switch ($request->getQueryParams()['format'] ?? 'sse') {
        case 'json':
            $view = new JsonView;
            $view->render((new Check)->execute($name));
            return;

        case 'sse':
            $view = new ServerSentEventView('response');
            $checker = new Check([$view, 'render']);
            $checker->execute($name);
            $view->close();
            return;
    }
});
$app->get('/list/[{name}/]', function (Request $request, Response $response, array $args) {
    $name = $args['name'] ?? null;
    $homos = isset($name) ? Homo::getByScreenName($name) : Homo::getAll();

    $view = new JsonView;
    $view->render(array_map(function (Homo $item): HomoResponse {
        return new HomoResponse($item);
    }, iterator_to_array($homos)));
});
$app->run();
