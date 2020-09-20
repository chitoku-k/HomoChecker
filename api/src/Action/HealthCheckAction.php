<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\HomoService;
use Illuminate\Support\Facades\Log;
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
            Log::error($e);
            return $response->withHeader('Content-Type', 'text/plain')->withStatus(500)->write('Internal Server Error');
        }
    }
}
