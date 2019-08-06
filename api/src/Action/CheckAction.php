<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Status;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;

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
     * @var StreamInterface
     */
    protected $stream;

    public function __construct(CheckService $check, HomoService $homo, StreamInterface $stream)
    {
        $this->check = $check;
        $this->homo = $homo;
        $this->stream = $stream;
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        $screen_name = $args['name'] ?? null;

        switch ($request->getQueryParams()['format'] ?? 'sse') {
            case 'json': {
                return $this->byJSON($response, $screen_name);
            }
            case 'sse':
            default: {
                return $this->bySSE($response, $screen_name);
            }
        }
    }

    protected function byJSON(Response $response, string $screen_name = null): Response
    {
        $result = $this->check->execute($screen_name);
        return $response->withJson($result, !empty($result) ? 200 : 404);
    }

    protected function bySSE(Response $response, string $screen_name = null): Response
    {
        $response = $response->withBody($this->stream)->withHeader('Content-Type', 'text/event-stream');

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
