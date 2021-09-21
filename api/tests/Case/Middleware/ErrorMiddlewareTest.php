<?php
declare(strict_types=1);

namespace HomoChecker\Test\Middleware;

use HomoChecker\Http\ErrorResponse;
use HomoChecker\Middleware\ErrorMiddleware;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class ErrorMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testProcessHttpException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/metrics');
        $exception = new HttpNotFoundException($request);
        $response = new ErrorResponse(new Response(), new StreamFactory());

        /** @var ErrorHandlerInterface|MockInterface $errorHandler */
        $errorHandler = m::mock(ErrorHandlerInterface::class);
        $errorHandler->shouldReceive('__invoke')
                     ->withArgs([$request, $exception, false, false, false])
                     ->andReturn($response);

        /** @var CallableResolverInterface|MockInterface $callableResolver */
        $callableResolver = m::mock(CallableResolverInterface::class);
        $callableResolver->shouldReceive('resolve')
                         ->withArgs([$errorHandler])
                         ->andReturn($errorHandler);

        /** @var MockInterface|ResponseFactoryInterface $responseFactory */
        $responseFactory = m::mock(ResponseFactoryInterface::class);

        /** @var MockInterface|RequestHandlerInterface $handler */
        $handler = m::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
                ->withArgs([$request])
                ->andThrow($exception);

        $middleware = new ErrorMiddleware($callableResolver, $responseFactory);
        $middleware->setDefaultErrorHandler($errorHandler);
        $actual = $middleware->process($request, $handler);

        $this->assertEquals($response, $actual);
    }

    public function testProcessException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/metrics');
        $response = new ErrorResponse(new Response(), new StreamFactory());
        $exception = new RuntimeException();

        /** @var ErrorHandlerInterface|MockInterface $errorHandler */
        $errorHandler = m::mock(ErrorHandlerInterface::class);
        $errorHandler->shouldReceive('__invoke')
                     ->withArgs([$request, $exception, false, false, false])
                     ->andReturn($response);

        /** @var CallableResolverInterface|MockInterface $callableResolver */
        $callableResolver = m::mock(CallableResolverInterface::class);
        $callableResolver->shouldReceive('resolve')
                         ->withArgs([$errorHandler])
                         ->andReturn($errorHandler);

        /** @var MockInterface|ResponseFactoryInterface $responseFactory */
        $responseFactory = m::mock(ResponseFactoryInterface::class);

        /** @var MockInterface|RequestHandlerInterface $handler */
        $handler = m::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
                ->withArgs([$request])
                ->andThrow($exception);

        $middleware = new ErrorMiddleware($callableResolver, $responseFactory);
        $middleware->setDefaultErrorHandler($errorHandler);
        $actual = $middleware->process($request, $handler);
        $expected = $response->withException($exception);

        $this->assertEquals($expected, $actual);
    }
}
