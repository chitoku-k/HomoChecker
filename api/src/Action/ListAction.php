<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as Response;
use Slim\Http\ServerRequest as Request;

class ListAction
{
    public function __construct(protected HomoService $homo)
    {
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        $screen_name = $args['name'] ?? null;

        return match ($request->getQueryParams()['format'] ?? 'json') {
            'sql' => $response->withHeader('Content-Type', 'application/sql')->withBody($this->createSql($response)),
            default => (
                match ($users = $this->homo->find($screen_name)) {
                    [] => $response->withJson([], 404),
                    default => $response->withJson($this->createArray($users), 200),
                }
            )
        };
    }

    protected function createSql(Response $response): StreamInterface
    {
        $body = $response->getBody();
        $body->write($this->homo->export());
        return $body;
    }

    protected function createArray(array $homos): array
    {
        return collect($homos)
            ->map(fn (\stdClass $item) => new Homo($item))
            ->map(fn (Homo $item) => new Status(['homo' => $item]))
            ->map(fn (Status $item) => $item->getHomoArray())
            ->toArray();
    }
}
