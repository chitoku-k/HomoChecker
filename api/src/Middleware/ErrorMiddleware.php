<?php
declare(strict_types=1);

namespace HomoChecker\Middleware;

use HomoChecker\Http\ErrorResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpSpecializedException;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware as ErrorMiddlewareBase;
use Throwable;

class ErrorMiddleware extends ErrorMiddlewareBase
{
    public function __construct(CallableResolverInterface $callableResolver, ResponseFactoryInterface $responseFactory)
    {
        parent::__construct($callableResolver, $responseFactory, false, false, false);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            /** @var ErrorResponse $response */
            $response = $this->handleException($request, $e);
            if ($e instanceof HttpSpecializedException) {
                return $response;
            }

            return $response->withException($e);
        }
    }
}
