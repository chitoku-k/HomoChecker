<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\CheckAction;
use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Result;
use HomoChecker\Domain\Status;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as HttpResponse;
use Slim\Http\ServerRequest as HttpRequest;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class CheckActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        parent::setUp();

        $this->users = [
            new Homo([
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
            ]),
            new Homo([
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
            ]),
            new Homo([
                'screen_name' => 'bar',
                'service' => 'mastodon',
                'url' => 'http://bar.example.com',
            ]),
        ];

        $this->statuses = [
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/1',
                ]),
                'result' => new Result([
                    'status' => 'OK',
                    'code' => '302 Found',
                    'http' => '2.0',
                    'ip' => '2001:db8::4545:1',
                    'url' => 'https://foo.example.com/1',
                    'duration' => 10,
                ]),
                'icon' => 'https://icon.example.com/1',
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                ]),
                'result' => new Result([
                    'status' => 'NG',
                    'code' => '404 Not Found',
                    'http' => '1.1',
                    'ip' => '2001:db8::4545:2',
                    'url' => 'https://foo.example.com/2',
                    'duration' => 20,
                ]),
                'icon' => 'https://icon.example.com/2',
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                ]),
                'result' => new Result([
                    'status' => 'OK',
                    'code' => '200 OK',
                    'http' => '1.1',
                    'ip' => '2001:db8::4545:3',
                    'url' => 'https://bar.example.com/2',
                    'duration' => 30,
                ]),
                'icon' => 'https://icon.example.com/3',
            ]),
        ];
    }

    public function testRouteToJSON(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/check?format=json');

        /** @var CheckService|MockInterface $check */
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->with(null)
              ->andReturn($this->statuses);

        /** @var HomoService|MockInterface $homo */
        $homo = m::mock(HomoService::class);

        /** @var StreamInterface $stream */
        $stream = m::mock(StreamInterface::class);

        $action = new CheckAction($check, $homo, $stream);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/json|', $actual);

        $actual = $response->getHeaderLine('Cache-Control');
        $this->assertEquals('no-store', $actual);

        $actual = (string) $response->getBody();
        $expected = json_encode([
            [
                'homo' => [
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/1',
                    'display_url' => 'foo.example.com/1',
                    'secure' => true,
                    'icon' => 'https://icon.example.com/1',
                ],
                'status' => 'OK',
                'code' => '302 Found',
                'http' => '2.0',
                'ip' => '2001:db8::4545:1',
                'url' => 'https://foo.example.com/1',
                'duration' => 10,
                'error' => null,
            ],
            [
                'homo' => [
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                    'display_url' => 'foo.example.com/2',
                    'secure' => true,
                    'icon' => 'https://icon.example.com/2',
                ],
                'status' => 'NG',
                'code' => '404 Not Found',
                'http' => '1.1',
                'ip' => '2001:db8::4545:2',
                'url' => 'https://foo.example.com/2',
                'duration' => 20,
                'error' => null,
            ],
            [
                'homo' => [
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                    'display_url' => 'bar.example.com',
                    'secure' => false,
                    'icon' => 'https://icon.example.com/3',
                ],
                'status' => 'OK',
                'code' => '200 OK',
                'http' => '1.1',
                'ip' => '2001:db8::4545:3',
                'url' => 'https://bar.example.com/2',
                'duration' => 30,
                'error' => null,
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @dataProvider formatProvider
     */
    public function testRouteToSSE(mixed $format = null): void
    {
        $request = (new RequestFactory())->createRequest('GET', "/check?format={$format}");

        /** @var HomoService|MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->with(null)
             ->andReturn(3);

        /** @var MockInterface|StreamInterface $stream */
        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('write')
               ->once()
               ->with("event: initialize\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"count\":3}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"foo\",\"service\":\"twitter\",\"url\":\"https:\\/\\/foo.example.com\\/1\",\"display_url\":\"foo.example.com\\/1\",\"secure\":true,\"icon\":\"https:\\/\\/icon.example.com\\/1\"},\"status\":\"OK\",\"code\":\"302 Found\",\"http\":\"2.0\",\"ip\":\"2001:db8::4545:1\",\"url\":\"https:\\/\\/foo.example.com\\/1\",\"duration\":10,\"error\":null}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"foo\",\"service\":\"twitter\",\"url\":\"https:\\/\\/foo.example.com\\/2\",\"display_url\":\"foo.example.com\\/2\",\"secure\":true,\"icon\":\"https:\\/\\/icon.example.com\\/2\"},\"status\":\"NG\",\"code\":\"404 Not Found\",\"http\":\"1.1\",\"ip\":\"2001:db8::4545:2\",\"url\":\"https:\\/\\/foo.example.com\\/2\",\"duration\":20,\"error\":null}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"bar\",\"service\":\"mastodon\",\"url\":\"http:\\/\\/bar.example.com\",\"display_url\":\"bar.example.com\",\"secure\":false,\"icon\":\"https:\\/\\/icon.example.com\\/3\"},\"status\":\"OK\",\"code\":\"200 OK\",\"http\":\"1.1\",\"ip\":\"2001:db8::4545:3\",\"url\":\"https:\\/\\/bar.example.com\\/2\",\"duration\":30,\"error\":null}\n\n");

        /** @var CheckService|MockInterface $check */
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->withArgs(function (?string $screen_name, callable $callback) {
                  $callback($this->statuses[0]);
                  $callback($this->statuses[1]);
                  $callback($this->statuses[2]);
                  return true;
              });

        $action = new CheckAction($check, $homo, $stream);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertEquals('text/event-stream', $actual);

        $actual = $response->getHeaderLine('Cache-Control');
        $this->assertEquals('no-store', $actual);
    }

    public function formatProvider()
    {
        return [
            'default' => [
                '',
            ],
            'sse' => [
                'sse',
            ],
        ];
    }
}
