<?php
declare(strict_types=1);

namespace HomoChecker\Middleware;

use HomoChecker\Http\ErrorResponse;
use Prometheus\Summary;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Routing\RoutingResults;

class MetricsMiddleware implements MiddlewareInterface
{
    public const INFORMATIONAL = 'INFORMATIONAL';
    public const SUCCESS = 'SUCCESS';
    public const REDIRECTION = 'REDIRECTION';
    public const CLIENT_ERROR = 'CLIENT_ERROR';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const UNKNOWN = 'UNKNOWN';

    /**
     * @param string[] $skipPaths Paths from which to skip collecting metrics.
     */
    public function __construct(
        protected Summary $httpServerRequestsSeconds,
        protected RouteResolverInterface $routeResolver,
        protected array $skipPaths,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (collect($this->skipPaths)->contains($request->getUri()->getPath())) {
            return $handler->handle($request);
        }

        $begin = microtime(true);
        $response = $handler->handle($request);
        $end = microtime(true);

        $this->httpServerRequestsSeconds->observe($end - $begin, [
            'method' => $request->getMethod(),
            'uri' => $this->getRoutePattern($request),
            'exception' => $this->getException($response),
            'status' => $response->getStatusCode(),
            'outcome' => $this->getOutcome($response->getStatusCode()),
        ]);

        return $response;
    }

    protected function getRoutePattern(ServerRequestInterface $request): string
    {
        $results = $this->routeResolver->computeRoutingResults($request->getUri()->getPath(), $request->getMethod());

        return match ($results->getRouteStatus()) {
            RoutingResults::FOUND => $this->routeResolver->resolveRoute($results->getRouteIdentifier())->getPattern(),
            default => '/**',
        };
    }

    protected function getOutcome(int $status): string
    {
        return match (true) {
            $status >= 100 && $status < 200 => static::INFORMATIONAL,
            $status >= 200 && $status < 300 => static::SUCCESS,
            $status >= 300 && $status < 400 => static::REDIRECTION,
            $status >= 400 && $status < 500 => static::CLIENT_ERROR,
            $status >= 500 && $status < 600 => static::SERVER_ERROR,
            default => static::UNKNOWN,
        };
    }

    protected function getException(ResponseInterface $response): string
    {
        if ($response instanceof ErrorResponse && $response->getException()) {
            return $response->getException()::class;
        }

        return 'None';
    }
}
