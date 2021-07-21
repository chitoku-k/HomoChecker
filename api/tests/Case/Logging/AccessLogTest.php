<?php
declare(strict_types=1);

namespace HomoChecker\Test\Logging;

use HomoChecker\Logging\AccessLog;
use Middlewares\AccessLog as AccessLogBase;
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

class AccessLogTest extends TestCase
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

        $log = new AccessLog($logger, ['/metrics']);
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

        /** @var AccessLogBase|MockInterface $base */
        $base = m::mock('overload:' . AccessLogBase::class);
        $base->shouldReceive('process')
             ->withArgs([$request, $handler])
             ->andReturn($response);

        $log = new AccessLog($logger, ['/metrics']);
        $actual = $log->process($request, $handler);

        $this->assertEquals($response, $actual);
    }
}
