<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service\Client;

use GuzzleHttp\Psr7\Response as Psr7Response;
use HomoChecker\Contracts\Service\Client\Response;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);

        $actual = new Response($response);
        $actual->setTotalTime(1.0);
        $actual->setStartTransferTime(2.0);
        $actual->setCertificates([
            [
                'Subject' => 'CN = homo.example.com',
                'Issuer' => 'C = US, O = Amazon, OU = Server CA 1B, CN = Amazon',
                'Version' => '2',
                'Signature Algorithm' => 'sha256WithRSAEncryption',
                'Public Key Algorithm' => 'rsaEncryption',
                'X509v3 Subject Alternative Name' => 'DNS:*.homo.example.com, DNS:homo.example.com',
                'X509v3 Key Usage' => 'Digital Signature, Key Encipherment',
                'X509v3 Extended Key Usage' => 'TLS Web Server Authentication, TLS Web Client Authentication',
                'X509v3 Basic Constraints' => 'CA:FALSE',
                'Start date' => 'Jul  1 00:00:00 2022 GMT',
                'Expire date' => 'Jul 30 23:59:59 2023 GMT',
                'RSA Public Key' => '2048',
            ],
        ]);
        $actual->setHttpVersion(null);
        $actual->setPrimaryIP('2001:db8::4545:1');

        $this->assertEquals(1.0, $actual->getTotalTime());
        $this->assertEquals(2.0, $actual->getStartTransferTime());
        $this->assertEquals([
            [
                'subject' => 'CN = homo.example.com',
                'issuer' => 'C = US, O = Amazon, OU = Server CA 1B, CN = Amazon',
                'subjectAlternativeName' => [
                    '*.homo.example.com',
                    'homo.example.com',
                ],
                'notBefore' => 'Jul  1 00:00:00 2022 GMT',
                'notAfter' => 'Jul 30 23:59:59 2023 GMT',
            ],
        ], $actual->getCertificates());
        $this->assertEquals(null, $actual->getHttpVersion());
        $this->assertEquals('2001:db8::4545:1', $actual->getPrimaryIP());
    }

    public function testConstructHttp10(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);

        $actual = new Response($response);
        $actual->setHttpVersion(CURL_HTTP_VERSION_1_0);

        $this->assertEquals('1.0', $actual->getHttpVersion());
    }

    public function testConstructHttp11(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);

        $actual = new Response($response);
        $actual->setHttpVersion(CURL_HTTP_VERSION_1_1);

        $this->assertEquals('1.1', $actual->getHttpVersion());
    }

    public function testConstructHttp2(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);

        $actual = new Response($response);
        $actual->setHttpVersion(CURL_HTTP_VERSION_2);

        $this->assertEquals('2', $actual->getHttpVersion());
    }

    public function testConstructHttp3(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);

        $actual = new Response($response);
        $actual->setHttpVersion(CURL_HTTP_VERSION_3);

        $this->assertEquals('3', $actual->getHttpVersion());
    }

    public function testGetStatus(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('getStatusCode')
                 ->andReturn(404);

        $actual = new Response($response);

        $this->assertEquals(404, $actual->getStatusCode());
    }

    public function testGetReasonPhrase(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('getReasonPhrase')
                 ->andReturn('Not Found');

        $actual = new Response($response);

        $this->assertEquals('Not Found', $actual->getReasonPhrase());
    }

    public function testWithStatus(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('withStatus')
                 ->withArgs([302, 'Found'])
                 ->andReturn($response);

        $actual1 = new Response($response);
        $actual2 = $actual1->withStatus(302, 'Found');

        $this->assertNotSame($actual1, $actual2);
        $this->assertInstanceOf(Response::class, $actual2);
    }

    public function testGetProtocolVersion(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('getProtocolVersion')
                 ->andReturn('1.1');

        $actual = new Response($response);

        $this->assertEquals('1.1', $actual->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('withProtocolVersion')
                 ->withArgs(['1.0'])
                 ->andReturn($response);

        $actual1 = new Response($response);
        $actual2 = $actual1->withProtocolVersion('1.0');

        $this->assertNotSame($actual1, $actual2);
        $this->assertInstanceOf(Response::class, $actual2);
    }

    public function testGetHeaders(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('getHeaders')
                 ->andReturn(['Location' => 'https://example.com']);

        $actual = new Response($response);

        $this->assertEquals(['Location' => 'https://example.com'], $actual->getHeaders());
    }

    public function testHasHeader(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('hasHeader')
                 ->withArgs(['Location'])
                 ->andReturn(true);

        $actual = new Response($response);

        $this->assertTrue($actual->hasHeader('Location'));
    }

    public function testGetHeader(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('getHeader')
                 ->withArgs(['Location'])
                 ->andReturn(['https://example.com']);

        $actual = new Response($response);

        $this->assertEquals(['https://example.com'], $actual->getHeader('Location'));
    }

    public function testWithHeader(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('withHeader')
                 ->withArgs(['Location', 'https://example.com'])
                 ->andReturn($response);

        $actual1 = new Response($response);
        $actual2 = $actual1->withHeader('Location', 'https://example.com');

        $this->assertNotSame($actual1, $actual2);
        $this->assertInstanceOf(Response::class, $actual2);
    }

    public function testWithAddedHeader(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('withAddedHeader')
                 ->withArgs(['Location', 'https://example.com'])
                 ->andReturn($response);

        $actual1 = new Response($response);
        $actual2 = $actual1->withAddedHeader('Location', 'https://example.com');

        $this->assertNotSame($actual1, $actual2);
        $this->assertInstanceOf(Response::class, $actual2);
    }

    public function testWithoutHeader(): void
    {
        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('withoutHeader')
                 ->withArgs(['Location'])
                 ->andReturn($response);

        $actual1 = new Response($response);
        $actual2 = $actual1->withoutHeader('Location');

        $this->assertNotSame($actual1, $actual2);
        $this->assertInstanceOf(Response::class, $actual2);
    }

    public function testGetBody(): void
    {
        /** @var MockInterface&StreamInterface $body */
        $body = m::mock(StreamInterface::class);

        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('getBody')
                 ->andReturn($body);

        $actual = new Response($response);

        $this->assertEquals($body, $actual->getBody());
    }

    public function testWithBody(): void
    {
        /** @var MockInterface&StreamInterface $body */
        $body = m::mock(StreamInterface::class);

        /** @var MockInterface&Response $response */
        $response = m::mock(Psr7Response::class);
        $response->shouldReceive('withBody')
                 ->withArgs([$body])
                 ->andReturn($response);

        $actual1 = new Response($response);
        $actual2 = $actual1->withBody($body);

        $this->assertNotSame($actual1, $actual2);
        $this->assertInstanceOf(Response::class, $actual2);
    }
}
