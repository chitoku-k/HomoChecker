<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Status;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class CheckAction
{
    public function __construct(protected CheckService $check, protected HomoService $homo, protected StreamInterface $stream) {}

    public function __invoke(Request $request, Response $response, array $args)
    {
        $screen_name = $args['name'] ?? null;

        return match ($request->getQueryParam('format')) {
            'json' => $this->byJSON($response, $screen_name),
            default => $this->bySSE($response, $screen_name),
        };
    }

    protected function byJSON(Response $response, string $screen_name = null): Response
    {
        $result = $this->check->execute($screen_name);
        return $response
            ->withHeader('Cache-Control', 'no-store')
            ->withJson($result, !empty($result) ? 200 : 404);
    }

    protected function bySSE(Response $response, string $screen_name = null): Response
    {
        $response = $response
            ->withBody($this->stream)
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-store');

        // Output count
        $count = [
            'count' => $this->homo->count($screen_name),
        ];

        $response->getBody()->write("event: initialize\n");
        $response->getBody()->write('data: ' . json_encode($count) . "\n\n");

        // Output response
        $this->check->execute($screen_name, function (Status $status) use ($response) {
            $response->getBody()->write("event: response\n");
            $response->getBody()->write('data: ' . json_encode($status) . "\n\n");
        });

        return $response;
    }
}
