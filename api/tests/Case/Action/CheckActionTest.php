<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use HomoChecker\Action\CheckAction;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\Status;
use HomoChecker\Utilities\Container;
use PHPUnit\Framework\TestCase;

class CheckActionTest extends TestCase
{
    protected function user(string $screen_name, string $service, string $url): Homo
    {
        $homo = new Homo();
        $homo->screen_name = $screen_name;
        $homo->service = $service;
        $homo->url = $url;
        return $homo;
    }

    public function setUp()
    {
        parent::setUp();
        $users = [
            $this->user('foo', 'twitter', 'https://foo.example.com/1'),
            $this->user('foo', 'twitter', 'https://foo.example.com/2'),
            $this->user('bar', 'mastodon', 'http://bar.example.com'),
        ];
        $statuses = [
            new Status($this->user('foo', 'twitter', 'https://foo.example.com/1'), null, 'OK', '2001:db8::4545:1', 10),
            new Status($this->user('foo', 'twitter', 'https://foo.example.com/2'), null, 'NG', '2001:db8::4545:2', 20),
            new Status($this->user('bar', 'mastodon', 'http://bar.example.com'), null, 'OK', '2001:db8::4545:3', 30),
        ];

        $this->Container = new Container();
        $this->Container['homo'] = $this->createMock(Homo::class);
        $this->Container['homo']->expects($this->any())
                                ->method('find')
                                ->will($this->returnValue($users));

        $this->Container['checker'] = $this->createMock(Check::class);
        $this->Container['checker']->expects($this->any())
                                   ->method('execute')
                                   ->will($this->returnValue($statuses));
    }

    public function testRouteToJSON(): void
    {
        $action = new CheckAction($this->Container);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/check',
            'QUERY_STRING' => 'format=json',
        ]));

        $response = $action($request, new Response(), []);
        $actual = $response->getHeaderLine('Content-Type');
        $this->assertRegExp('|^application/json|', $actual);

        $actual = (string)$response->getBody();
        $expected = json_encode([
            [
                'homo' => [
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/1',
                    'display_url' => 'foo.example.com/1',
                    'secure' => true,
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
     */
    public function testRouteToSSE($format = null): void
    {
        $action = new CheckAction($this->Container);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/check',
            'QUERY_STRING' => "format={$format}",
        ]));

        // This test does not receive any results because mocked Check::execute
        // never calls ServerSentEventView::render. This test could be
        // implemented if the method were replaced using DI.
        $this->markTestIncomplete('The test for SSE is not yet implemented.');

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE);
        $action($request, new Response(), []);
        $body = ob_get_clean();

        foreach (preg_split("/\r\n\r\n/", $body) as $actual) {
        }
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
