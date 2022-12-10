<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\HealthCheckAction;
use HomoChecker\Contracts\Service\HomoService;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response as HttpResponse;
use Slim\Http\ServerRequest as HttpRequest;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class HealthCheckActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        Facade::clearResolvedInstances();
    }

    public function testOK(): void
    {
        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->andReturn(3);

        $action = new HealthCheckAction($homo);
        $request = (new RequestFactory())->createRequest('GET', '/healthz');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);
        $actual = $response->getStatusCode();
        $this->assertEquals(200, $actual);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertEquals('text/plain', $actual);

        $actual = (string) $response->getBody();
        $this->assertEquals('OK', $actual);
    }

    public function testInternalServerError(): void
    {
        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->andThrow($e = new \Exception('Internal Server Error'));

        Log::shouldReceive('error')
            ->once()
            ->with($e);

        $action = new HealthCheckAction($homo);
        $request = (new RequestFactory())->createRequest('GET', '/healthz');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);
        $actual = $response->getStatusCode();
        $this->assertEquals(500, $actual);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertEquals('text/plain', $actual);

        $actual = (string) $response->getBody();
        $this->assertEquals('Internal Server Error', $actual);
    }
}
