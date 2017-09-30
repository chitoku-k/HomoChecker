<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use HomoChecker\Action\ListAction;
use HomoChecker\Model\Homo;
use HomoChecker\Utilities\Container;
use PHPUnit\Framework\TestCase;

class ListActionTest extends TestCase
{
    protected function user(string $screen_name, string $url): Homo
    {
        $homo = new Homo;
        $homo->screen_name = $screen_name;
        $homo->url = $url;
        return $homo;
    }

    public function setUp()
    {
        $users = [
            $this->user('foo', 'https://foo.example.com/1'),
            $this->user('foo', 'https://foo.example.com/2'),
            $this->user('bar', 'http://bar.example.com'),
        ];

        $this->Container = new Container;
        $this->Container['homo'] = $this->createMock(Homo::class);
        $this->Container['homo']->expects($this->any())
                                ->method('find')
                                ->will($this->returnValue($users));
    }

    public function testList()
    {
        $action = new ListAction($this->Container);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/list',
        ]));

        $response = $action($request, new Response, []);
        $actual = $response->getHeaderLine('Content-Type');
        $this->assertRegExp('|^application/json|', $actual);

        $actual = (string)$response->getBody();
        $users = [
            [
                'screen_name' => 'foo',
                'url' => 'https://foo.example.com/1',
                'display_url' => 'foo.example.com/1',
                'secure' => true,
            ],
            [
                'screen_name' => 'foo',
                'url' => 'https://foo.example.com/2',
                'display_url' => 'foo.example.com/2',
                'secure' => true,
            ],
            [
                'screen_name' => 'bar',
                'url' => 'http://bar.example.com',
                'display_url' => 'bar.example.com',
                'secure' => false,
            ],
        ];

        $expected = json_encode($users);
        $this->assertJsonStringEqualsJsonString($actual, $expected);
    }
}
