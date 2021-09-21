<?php
declare(strict_types=1);

namespace HomoChecker\Test\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use HomoChecker\Http\ErrorResponse;
use HomoChecker\Middleware\MetricsMiddleware;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PDOException;
use PHPUnit\Framework\TestCase;
use Prometheus\Summary;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Response as HttpResponse;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\DispatcherInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;
use Slim\Routing\Route;
use Slim\Routing\RoutingResults;

class MetricsMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testProcessSkip(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/metrics');
        $response = new HttpResponse(new Response(), new StreamFactory());

        /** @var MockInterface|Summary $summary */
        $summary = m::mock(Summary::class);

        /** @var MockIntercae|RouteResolverInterface $routeResolver */
        $routeResolver = m::mock(RouteResolverInterface::class);

        /** @var MockInterface|RequestHandlerInterface $handler */
        $handler = m::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
                ->withArgs([$request])
                ->andReturn($response);

        $middleware = new MetricsMiddleware($summary, $routeResolver, ['/metrics']);
        $actual = $middleware->process($request, $handler);

        $this->assertEquals($response, $actual);
    }

    public function testProcessHttpNotFoundException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/not_found');
        $response = new ErrorResponse(new Response(StatusCodeInterface::STATUS_NOT_FOUND), new StreamFactory());

        /** @var MockInterface|Summary $summary */
        $summary = m::mock(Summary::class);
        $summary->shouldReceive('observe')
                ->withArgs(function (float $value, array $labels): bool {
                    if ($value <= 0 || $value > 1) {
                        return false;
                    }
                    return $labels === [
                        'method' => 'GET',
                        'uri' => '/**',
                        'exception' => 'None',
                        'status' => 404,
                        'outcome' => 'CLIENT_ERROR',
                    ];
                })
                ->andReturn();

        /** @var DispatcherInterface|MockInterface $dispatcher */
        $dispatcher = m::mock(DispatcherInterface::class);

        $results = new RoutingResults($dispatcher, 'GET', '/not_found', RoutingResults::NOT_FOUND);

        /** @var MockInterface|RouteResolverInterface $routeResolver */
        $routeResolver = m::mock(RouteResolverInterface::class);
        $routeResolver->shouldReceive('computeRoutingResults')
                      ->withArgs(['/not_found', 'GET'])
                      ->andReturn($results);

        /** @var MockInterface|RequestHandlerInterface $handler */
        $handler = m::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
                ->withArgs([$request])
                ->andReturn($response);

        $middleware = new MetricsMiddleware($summary, $routeResolver, ['/metrics']);
        $actual = $middleware->process($request, $handler);

        $this->assertEquals($response, $actual);
    }

    public function testProcessHttpInternalServerErrorException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/list');
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');
        $response = (new ErrorResponse(new Response(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR), new StreamFactory()))->withException($exception);

        /** @var MockInterface|Summary $summary */
        $summary = m::mock(Summary::class);
        $summary->shouldReceive('observe')
                ->withArgs(function (float $value, array $labels): bool {
                    if ($value <= 0 || $value > 1) {
                        return false;
                    }
                    return $labels === [
                        'method' => 'GET',
                        'uri' => '/list[/[{name}[/]]]',
                        'exception' => 'PDOException',
                        'status' => 500,
                        'outcome' => 'SERVER_ERROR',
                    ];
                })
                ->andReturn();

        /** @var DispatcherInterface|MockInterface $dispatcher */
        $dispatcher = m::mock(DispatcherInterface::class);

        /** @var CallableResolverInterface|MockInterface $callableResolver */
        $callableResolver = m::mock(CallableResolverInterface::class);

        /** @var MockInterface|ResponseFactoryInterface $responseFactory */
        $responseFactory = m::mock(ResponseFactoryInterface::class);

        $results = new RoutingResults($dispatcher, 'GET', '/list', RoutingResults::FOUND, 'route0', []);
        $route = new Route(['GET'], '/list[/[{name}[/]]]', fn () => null, $responseFactory, $callableResolver);

        /** @var MockInterface|RouteResolverInterface $routeResolver */
        $routeResolver = m::mock(RouteResolverInterface::class);
        $routeResolver->shouldReceive('computeRoutingResults')
                      ->withArgs(['/list', 'GET'])
                      ->andReturn($results);
        $routeResolver->shouldReceive('resolveRoute')
                      ->withArgs(['route0'])
                      ->andReturn($route);

        /** @var MockInterface|RequestHandlerInterface $handler */
        $handler = m::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
                ->withArgs([$request])
                ->andReturn($response);

        $middleware = new MetricsMiddleware($summary, $routeResolver, ['/metrics']);
        $actual = $middleware->process($request, $handler);

        $this->assertEquals($response, $actual);
    }
}
