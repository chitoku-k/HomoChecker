<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\HomoResponse;
use HomoChecker\View\ServerSentEventView;

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
    'settings' => [
        'outputBuffering'        => false,
        'addContentLengthHeader' => false,
    ],
]);
$app->get('/check/[{name}/]', function (Request $request, Response $response, array $args) {
    $name = $args['name'] ?? null;

    switch ($request->getQueryParams()['format'] ?? 'sse') {
        case 'json':
            $result = (new Check)->execute($name);
            return $response->withJson($result, !empty($result) ? 200 : 404);

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
    $homos = iterator_to_array(isset($name) ? Homo::getByScreenName($name) : Homo::getAll());

    return $response->withJson(array_map(function (Homo $item): array {
        return (new HomoResponse($item))->homo;
    }, $homos), !empty($homos) ? 200 : 404);
});
$app->get('/badge/[{status}/]', function (Request $request, Response $response, array $args) {
    if (!isset($args['status'])) {
        $count = iterator_count(Homo::getAll());
    } else {
        $count = count(array_filter((new Check)->execute(), function ($result) use ($args) {
            return strcasecmp($result->status, $args['status']) === 0;
        }));
    }

    $query = http_build_query($request->getParams());
    $label = "{$count} " . strtolower($args['status'] ?? 'registered');
    $uri = "https://img.shields.io/badge/homo-{$label}-7a6544.svg" . ($query ? "?{$query}" : '');
    return $response->withRedirect($uri);
});
$app->run();
