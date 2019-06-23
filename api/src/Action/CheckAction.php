<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Contracts\View\ServerSentEventView;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CheckAction
{
    /**
     * @var CheckService
     */
    protected $check;

    /**
     * @var HomoService
     */
    protected $homo;

    /**
     * @var ServerSentEventView
     */
    protected $sse;

    public function __construct(CheckService $check, HomoService $homo, ServerSentEventView $sse)
    {
        $this->check = $check;
        $this->homo = $homo;
        $this->sse = $sse;
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        $screen_name = $args['name'] ?? null;

        switch ($request->getQueryParams()['format'] ?? 'sse') {
            case 'json': {
                return $this->byJSON($response, $screen_name);
            }
            case 'sse': {
                return $this->bySSE($response, $screen_name);
            }
        }
    }

    protected function byJSON(Response $response, string $screen_name = null): Response
    {
        $result = $this->check->execute($screen_name);
        return $response->withJson($result, !empty($result) ? 200 : 404);
    }

    protected function bySSE(Response $response, string $screen_name = null): void
    {
        // Output count
        $this->sse->render(
            ['count' => $this->homo->count($screen_name)],
            'initialize',
        );

        // Output response
        $this->check->execute($screen_name, [$this->sse, 'render']);

        // Close
        $this->sse->close();
    }
}
