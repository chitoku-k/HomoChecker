<?php
namespace HomoChecker\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Check;
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
        $result = (new Check)->execute($name);
        return $this->container['response']->withJson($result, !empty($result) ? 200 : 404);
    }

    protected function bySSE(string $name = null)
    {
        $view = new ServerSentEventView('response');
        $checker = new Check([$view, 'render']);
        $checker->execute($name);
        $view->close();
    }
}
