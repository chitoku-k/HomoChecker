<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\HomoService;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class HealthCheckAction
{
    protected HomoService $homo;

    public function __construct(HomoService $homo)
    {
        $this->homo = $homo;
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        try {
            $this->homo->count();
            return $response->withHeader('Content-Type', 'text/plain')->withStatus(200)->write('OK');
        } catch (\Throwable $e) {
            // TODO: Log using illuminate/log
            return $response->withHeader('Content-Type', 'text/plain')->withStatus(500)->write('Internal Server Error');
        }
    }
}
