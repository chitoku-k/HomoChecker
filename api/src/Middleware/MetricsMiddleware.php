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
    public const string INFORMATIONAL = 'INFORMATIONAL';
    public const string SUCCESS = 'SUCCESS';
    public const string REDIRECTION = 'REDIRECTION';
    public const string CLIENT_ERROR = 'CLIENT_ERROR';
    public const string SERVER_ERROR = 'SERVER_ERROR';
    public const string UNKNOWN = 'UNKNOWN';

    /**
     * @param string[] $skipPaths Paths from which to skip collecting metrics.
     */
    public function __construct(
        protected Summary $httpServerRequestsSeconds,
        protected RouteResolverInterface $routeResolver,
        protected array $skipPaths,
    ) {}

    #[\Override]
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
            'status' => (string) $response->getStatusCode(),
            'outcome' => $this->getOutcome($response->getStatusCode()),
        ]);

        return $response;
    }

    protected function getRoutePattern(ServerRequestInterface $request): string
    {
        $results = $this->routeResolver->computeRoutingResults($request->getUri()->getPath(), $request->getMethod());
        if ($results->getRouteStatus() !== RoutingResults::FOUND || !$id = $results->getRouteIdentifier()) {
            return '/**';
        }

        return $this->routeResolver->resolveRoute($id)->getPattern();
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
