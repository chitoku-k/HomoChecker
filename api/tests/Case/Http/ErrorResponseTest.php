<?php
declare(strict_types=1);

namespace HomoChecker\Test\Http;

use HomoChecker\Http\ErrorResponse;
use Mockery as m;
use Mockery\MockInterface;
use PDOException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as HttpResponse;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class ErrorResponseTest extends TestCase
{
    public function testGetException(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);

        $actual = $errorResponse->getException();
        $this->assertNull($actual);
    }

    public function testWithException(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithAddedHeader(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withAddedHeader('Location', 'https://example.com');

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithBody(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        /** @var MockInterface|StreamInterface */
        $body = m::mock(StreamInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withBody($body);

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithHeader(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withHeader('Location', 'https://example.com');

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithoutHeader(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withoutHeader('Location');

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithProtocolVersion(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withProtocolVersion('1.1');

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithStatus(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withStatus(500, 'Internal server error');

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithJson(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamInterface */
        $body = m::mock(StreamInterface::class);

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);
        $streamFactory->shouldReceive('createStream')
                      ->withArgs(['{"errors":[{"code":500,"message":"Internal server error"}]}'])
                      ->andReturn($body);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withJson([
            'errors' => [
                [
                    'code' => 500,
                    'message' => 'Internal server error',
                ],
            ],
        ]);

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithRedirect(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withRedirect('https://example.com');

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithFileDownload(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamInterface */
        $body = m::mock(StreamInterface::class);
        $body->shouldReceive('getMetadata')
             ->andReturn([]);

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withFileDownload($body);

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }

    public function testWithFile(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());
        $exception = new PDOException('SQLSTATE[08006] [7] could not translate host name "database" to address: Name or service not known');

        /** @var MockInterface|StreamInterface */
        $body = m::mock(StreamInterface::class);
        $body->shouldReceive('getMetadata')
             ->andReturn([]);

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        $errorResponse = new ErrorResponse($response, $streamFactory);
        $errorResponse = $errorResponse->withException($exception);
        $errorResponse = $errorResponse->withFile($body);

        $actual = $errorResponse->getException();
        $this->assertEquals($exception, $actual);
    }
}
