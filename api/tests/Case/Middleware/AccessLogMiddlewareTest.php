<?php
declare(strict_types=1);

namespace HomoChecker\Test\Middleware;

use HomoChecker\Middleware\AccessLogMiddleware;
use Middlewares\AccessLog;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Response as HttpResponse;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class AccessLogMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     */
    public function testProcessSkip(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/metrics');
        $response = new HttpResponse(new Response(), new StreamFactory());

        /** @var LoggerInterface|MockInterface $logger */
        $logger = m::mock(LoggerInterface::class);

        /** @var MockInterface|RequestHandlerInterface $handler */
        $handler = m::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
                ->withArgs([$request])
                ->andReturn($response);

        $log = new AccessLogMiddleware($logger, ['/metrics']);
        $actual = $log->process($request, $handler);

        $this->assertEquals($response, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testProcessLog(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/check');
        $response = new HttpResponse(new Response(), new StreamFactory());

        /** @var LoggerInterface|MockInterface $logger */
        $logger = m::mock(LoggerInterface::class);

        /** @var MockInterface|RequestHandlerInterface $handler */
        $handler = m::mock(RequestHandlerInterface::class);

        /** @var AccessLog|MockInterface $base */
        $base = m::mock('overload:' . AccessLog::class);
        $base->shouldReceive('process')
             ->withArgs([$request, $handler])
             ->andReturn($response);

        $log = new AccessLogMiddleware($logger, ['/metrics']);
        $actual = $log->process($request, $handler);

        $this->assertEquals($response, $actual);
    }
}
