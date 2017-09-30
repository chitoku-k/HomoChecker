<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\View\ServerSentEventView;

class CheckAction extends ActionBase
{
    public function route(Request $request, Response $response, array $args)
    {
        $name = $args['name'] ?? null;

        switch ($request->getQueryParams()['format'] ?? 'sse') {
            case 'json':
                return $this->byJSON($name);

            case 'sse':
                return $this->bySSE($name);
        }
    }

    protected function byJSON(string $name = null): Response
    {
        $result = $this->container['checker']->execute($name);
        return $this->container['response']->withJson($result, !empty($result) ? 200 : 404);
    }

    protected function bySSE(string $name = null): void
    {
        $users = $this->container['homo']->find($name ? compact('name') : []);

        // Output count
        $view = new ServerSentEventView('response');
        $view->render(
            [
                'count' => count($users),
            ],
            'initialize'
        );

        // Output response
        $this->container['checker']->execute($name, [$view, 'render']);

        // Close
        $view->close();
    }
}
