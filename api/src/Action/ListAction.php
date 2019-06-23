<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use Illuminate\Support\Collection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListAction
{
    /**
     * @var HomoService
     */
    protected $homo;

    public function __construct(HomoService $homo)
    {
        $this->homo = $homo;
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        switch ($request->getQueryParams()['format'] ?? 'json') {
            case 'sql': {
                return $response->withHeader('Content-Type', 'application/sql')->withBody($this->createSql($response));
            }
            default: {
                $users = $this->homo->find();
                return $response->withJson($this->createArray($users), !empty($users) ? 200 : 404);
            }
        }
    }

    protected function createSql(Response $response)
    {
        $body = $response->getBody();
        $body->write($this->homo->export());
        return $body;
    }

    protected function createArray(array $homos): array
    {
        return array_map(function (\stdClass $item): array {
            $homo = new Homo($item);
            $status = new Status(compact('homo'));
            return $status->getHomoArray();
        }, $homos);
    }
}
