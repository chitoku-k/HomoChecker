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

        /** @var CheckService $check */
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->with(null)
              ->andReturn($this->statuses);

        /** @var HomoService $homo */
        $homo = m::mock(HomoService::class);

        /** @var ServerSentEventView $sse */
        $sse = m::mock(ServerSentEventView::class);

        $action = new CheckAction($check, $homo, $sse);
        $response = $action($request, new HttpResponse(new Response(), new StreamFactory()), []);
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
        $request = (new RequestFactory())->createRequest('GET', "/check?format={$format}");

        /** @var ServerSentEventView $sse */
        $sse = m::mock(ServerSentEventView::class);
        $sse->shouldReceive('render')
            ->with(['count' => 3], 'initialize');
        $sse->shouldReceive('close');

        /** @var CheckService $check */
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->with(null, [$sse, 'render']);

        /** @var HomoService $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->with(null)
             ->andReturn(3);

        if ($format === 'sse') {
            $this->markTestIncomplete('N/A');
            return;
        }

        $action = new CheckAction($check, $homo, $sse);
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
