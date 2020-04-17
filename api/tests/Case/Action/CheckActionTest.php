<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\CheckAction;
use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as HttpResponse;
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
                'icon' => 'https://icon.example.com/1',
                'status' => 'OK',
                'ip' => '2001:db8::4545:1',
                'duration' => 10,
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                ]),
                'icon' => 'https://icon.example.com/2',
                'status' => 'NG',
                'ip' => '2001:db8::4545:2',
                'duration' => 20,
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                ]),
                'icon' => 'https://icon.example.com/3',
                'status' => 'OK',
                'ip' => '2001:db8::4545:3',
                'duration' => 30,
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
        $response = $action($request, new HttpResponse(new Response(), new StreamFactory()), []);
        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/json|', $actual);

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
                'ip' => '2001:db8::4545:1',
                'duration' => 10,
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
                'ip' => '2001:db8::4545:2',
                'duration' => 20,
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
                'ip' => '2001:db8::4545:3',
                'duration' => 30,
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($actual, $expected);
    }

    /**
     * @dataProvider formatProvider
     * @param null|mixed $format
     */
    public function testRouteToSSE($format = null): void
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
               ->with("data: {\"homo\":{\"screen_name\":\"foo\",\"service\":\"twitter\",\"url\":\"https:\\/\\/foo.example.com\\/1\",\"display_url\":\"foo.example.com\\/1\",\"secure\":true,\"icon\":\"https:\\/\\/icon.example.com\\/1\"},\"status\":\"OK\",\"ip\":\"2001:db8::4545:1\",\"duration\":10}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"foo\",\"service\":\"twitter\",\"url\":\"https:\\/\\/foo.example.com\\/2\",\"display_url\":\"foo.example.com\\/2\",\"secure\":true,\"icon\":\"https:\\/\\/icon.example.com\\/2\"},\"status\":\"NG\",\"ip\":\"2001:db8::4545:2\",\"duration\":20}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"bar\",\"service\":\"mastodon\",\"url\":\"http:\\/\\/bar.example.com\",\"display_url\":\"bar.example.com\",\"secure\":false,\"icon\":\"https:\\/\\/icon.example.com\\/3\"},\"status\":\"OK\",\"ip\":\"2001:db8::4545:3\",\"duration\":30}\n\n");

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
        $action($request, new HttpResponse(new Response(), new StreamFactory()), []);
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
