<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpSpecializedException;
use Slim\Http\Response as HttpResponse;
use Throwable;

class HomoHandlerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('errorHandler', function (Container $app) {
            return function (Request $request, Throwable $exception) use ($app): Response {
                /** @var App $slim */
                $slim = $app->make('app');
                error_log((string) $exception);

                if (!$exception instanceof HttpSpecializedException) {
                    $exception = new HttpInternalServerErrorException($request, $exception->getMessage());
                }

                /** @var HttpResponse $response */
                $response = $slim->getResponseFactory()->createResponse($exception->getCode());
                return $response->withJson([
                    'errors' => [
                        'code' => $response->getStatusCode(),
                        'message' => $response->getReasonPhrase(),
                    ],
                ]);
            };
        });
    }

    public function provides()
    {
        return [
            'errorHandler',
        ];
    }
}
