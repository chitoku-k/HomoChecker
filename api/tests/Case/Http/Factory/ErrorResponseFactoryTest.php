<?php
declare(strict_types=1);

namespace HomoChecker\Test\Http\Factory;

use HomoChecker\Http\ErrorResponse;
use HomoChecker\Http\Factory\ErrorResponseFactory;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Slim\Http\Response as HttpResponse;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class ErrorResponseFactoryTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $response = new HttpResponse(new Response(500), new StreamFactory());

        /** @var MockInterface|StreamFactoryInterface $streamFactory */
        $streamFactory = m::mock(StreamFactoryInterface::class);

        /** @var MockInterface|ResponseFactoryInterface $responseFactory */
        $responseFactory = m::mock(ResponseFactoryInterface::class);
        $responseFactory->shouldReceive('createResponse')
                        ->withArgs([500, 'Internal server error'])
                        ->andReturn($response);

        $errorResponseFactory = new ErrorResponseFactory($responseFactory, $streamFactory);
        $actual = $errorResponseFactory->createResponse(500, 'Internal server error');

        $expected = new ErrorResponse($response, $streamFactory);

        $this->assertEquals($expected, $actual);
    }
}
