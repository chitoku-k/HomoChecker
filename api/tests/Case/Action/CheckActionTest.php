<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\CheckAction;
use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Contracts\View\ServerSentEventView;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

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
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/check',
            'QUERY_STRING' => 'format=json',
        ]));

        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->with(null)
              ->andReturn($this->statuses);

        $homo = m::mock(HomoService::class);
        $sse = m::mock(ServerSentEventView::class);

        $action = new CheckAction($check, $homo, $sse);
        $response = $action($request, new Response(), []);
        $actual = $response->getHeaderLine('Content-Type');
        $this->assertRegExp('|^application/json|', $actual);

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
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/check',
            'QUERY_STRING' => "format={$format}",
        ]));

        $sse = m::mock(ServerSentEventView::class);
        $sse->shouldReceive('render')
            ->with(['count' => 3], 'initialize');
        $sse->shouldReceive('close');

        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->with(null, [$sse, 'render']);

        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->with(null)
             ->andReturn(3);

        $action = new CheckAction($check, $homo, $sse);
        $action($request, new Response(), []);
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
