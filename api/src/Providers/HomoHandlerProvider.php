<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomoHandlerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('errorHandler', $this->createErrorHandler(500));
        $this->app->singleton('phpErrorHandler', $this->createErrorHandler(500));
        $this->app->singleton('notFoundHandler', $this->createErrorHandler(404));
        $this->app->singleton('notAllowedHandler', $this->createErrorHandler(403));
    }

    protected function createErrorHandler(int $code)
    {
        return function () use ($code): callable {
            return function (Request $request, Response $response, $e = null) use ($code): Response {
                error_log((string) $e);
                $response = $response->withStatus($code);
                return $response->withJson([
                    'errors' => [
                        'code' => $response->getStatusCode(),
                        'message' => $response->getReasonPhrase(),
                    ],
                ]);
            };
        };
    }

    public function provides()
    {
        return [
            'errorHandler',
            'phpErrorHandler',
            'notFoundHandler',
            'notAllowedHandler',
        ];
    }
}
