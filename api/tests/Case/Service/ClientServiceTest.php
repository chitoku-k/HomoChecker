<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use HomoChecker\Contracts\Service\Client\Response;
use HomoChecker\Service\ClientService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetAsyncWithSingleRedirectAndReturn(): void
    {
        $client = new Client([
            'allow_redirects' => false,
            'http_errors' => false,
            'handler' => HandlerStack::create(new MockHandler([
                new Psr7Response(301, ['Location' => 'https://homo.example.com'], ''),
            ])),
            'transfer_time' => 1.0,
        ]);

        $service = new ClientService($client, 5);
        $generator = $service->getAsync('https://foo.example.com/1');

        $generator->rewind();
        $this->assertTrue($generator->valid());

        // (1/2) https://foo.example.com/1
        /** @var string $actual */
        $actual = $generator->key();
        $this->assertEquals('https://foo.example.com/1', $actual);

        /** @var PromiseInterface<Response> $actual */
        $actual = $generator->current();
        $this->assertInstanceOf(PromiseInterface::class, $actual);

        $actual = $actual->wait();
        /** @var Response $actual */
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertEquals('https://homo.example.com', $actual->getHeaderLine('Location'));
        $this->assertEquals(301, $actual->getStatusCode());
        $this->assertEquals(1.0, $actual->getTotalTime());
        $this->assertEquals(0.0, $actual->getStartTransferTime());
        $this->assertNull($actual->getPrimaryIP());

        $generator->next();
        $this->assertTrue($generator->valid());
    }

    public function testGetAsyncWithMultipleRedirectsAndReturn(): void
    {
        $client = new Client([
            'allow_redirects' => false,
            'http_errors' => false,
            'handler' => HandlerStack::create(new MockHandler([
                new Psr7Response(301, ['Location' => 'https://foo2.example.com'], ''),
                new Psr7Response(200, [], '
                   <!doctype html>
                   <title>Fail</title>
               '),
            ])),
            'transfer_time' => 1.0,
        ]);

        $service = new ClientService($client, 5);
        $generator = $service->getAsync('https://foo.example.com/2');

        $generator->rewind();
        $this->assertTrue($generator->valid());

        // (1/2) https://foo.example.com/2
        /** @var string $actual */
        $actual = $generator->key();
        $this->assertEquals('https://foo.example.com/2', $actual);

        /** @var PromiseInterface<Response> $actual */
        $actual = $generator->current();
        $this->assertInstanceOf(PromiseInterface::class, $actual);

        $actual = $actual->wait();
        /** @var Response $actual */
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertEquals('https://foo2.example.com', $actual->getHeaderLine('Location'));
        $this->assertEquals(301, $actual->getStatusCode());
        $this->assertEquals(1.0, $actual->getTotalTime());
        $this->assertEquals(0.0, $actual->getStartTransferTime());
        $this->assertNull($actual->getPrimaryIP());

        $generator->next();
        $this->assertTrue($generator->valid());

        // (2/2) https://foo2.example.com
        /** @var string $actual */
        $actual = $generator->key();
        $this->assertEquals('https://foo2.example.com', $actual);

        /** @var PromiseInterface<Response> $actual */
        $actual = $generator->current();
        $this->assertInstanceOf(PromiseInterface::class, $actual);

        $actual = $actual->wait();
        /** @var Response $actual */
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertEquals('', $actual->getHeaderLine('Location'));
        $this->assertEquals(200, $actual->getStatusCode());
        $this->assertEquals(1.0, $actual->getTotalTime());
        $this->assertEquals(0.0, $actual->getStartTransferTime());
        $this->assertNull($actual->getPrimaryIP());
        $this->assertStringContainsString('<title>Fail</title>', (string) $actual->getBody());

        $generator->next();
        $this->assertFalse($generator->valid());
    }

    public function testGetAsyncWithException(): void
    {
        $client = new Client([
            'allow_redirects' => false,
            'http_errors' => false,
            'handler' => HandlerStack::create(new MockHandler([
                new RequestException('Connection error', new Psr7Request('GET', '')),
            ])),
            'transfer_time' => 1.0,
        ]);

        $service = new ClientService($client, 5);
        $generator = $service->getAsync('https://baz.example.com');

        $generator->rewind();
        $this->assertTrue($generator->valid());

        // (1/1) https://baz.example.com
        /** @var string $actual */
        $actual = $generator->key();
        $this->assertEquals('https://baz.example.com', $actual);

        /** @var PromiseInterface<Response> $actual */
        $actual = $generator->current();
        $this->assertInstanceOf(PromiseInterface::class, $actual);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Connection error');
        $actual->wait();
    }

    public function testSend(): void
    {
        /** @var MockInterface|RequestInterface */
        $request = m::mock(RequestInterface::class);

        /** @var MockInterface|ResponseInterface */
        $response = m::mock(ResponseInterface::class);

        /** @var ClientInterface|MockInterface */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')
               ->withArgs([$request, ['http_errors' => false]])
               ->andReturn($response);

        $service = new ClientService($client, 5);
        $actual = $service->send($request, [
            'http_errors' => false,
        ]);

        $this->assertEquals($response, $actual);
    }

    public function testSendAsync(): void
    {
        /** @var MockInterface|RequestInterface */
        $request = m::mock(RequestInterface::class);

        /** @var MockInterface|PromiseInterface */
        $promise = m::mock(PromiseInterface::class);

        /** @var ClientInterface|MockInterface */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('sendAsync')
               ->withArgs([$request, ['http_errors' => false]])
               ->andReturn($promise);

        $service = new ClientService($client, 5);
        $actual = $service->sendAsync($request, [
            'http_errors' => false,
        ]);

        $this->assertEquals($promise, $actual);
    }

    public function testRequest(): void
    {
        /** @var MockInterface|ResponseInterface */
        $response = m::mock(ResponseInterface::class);

        /** @var ClientInterface|MockInterface */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('request')
               ->withArgs(['GET', 'https://example.com', ['http_errors' => false]])
               ->andReturn($response);

        $service = new ClientService($client, 5);
        $actual = $service->request('GET', 'https://example.com', [
            'http_errors' => false,
        ]);

        $this->assertEquals($response, $actual);
    }

    public function testRequestAsync(): void
    {
        /** @var MockInterface|PromiseInterface */
        $promise = m::mock(PromiseInterface::class);

        /** @var ClientInterface|MockInterface */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('requestAsync')
               ->withArgs(['GET', 'https://example.com', ['http_errors' => false]])
               ->andReturn($promise);

        $service = new ClientService($client, 5);
        $actual = $service->requestAsync('GET', 'https://example.com', [
            'http_errors' => false,
        ]);

        $this->assertEquals($promise, $actual);
    }

    public function testGetConfig(): void
    {
        /** @var ClientInterface|MockInterface */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('getConfig')
               ->andReturn(['http_errors' => false]);

        $service = new ClientService($client, 5);
        $actual = $service->getConfig();

        $this->assertEquals(['http_errors' => false], $actual);
    }
}
