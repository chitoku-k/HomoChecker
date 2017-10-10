<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use HomoChecker\Action\BadgeAction;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\Status;
use HomoChecker\Utilities\Container;
use PHPUnit\Framework\TestCase;

class BadgeActionTest extends TestCase
{
    protected function user(string $screen_name, string $url): Homo
    {
        $homo = new Homo();
        $homo->screen_name = $screen_name;
        $homo->url = $url;
        return $homo;
    }

    public function setUp()
    {
        parent::setUp();
        $users = [
            $this->user('foo', 'https://foo.example.com/1'),
            $this->user('foo', 'https://foo.example.com/2'),
            $this->user('bar', 'http://bar.example.com'),
        ];
        $statuses = [
            new Status($this->user('foo', 'https://foo.example.com/1'), null, 'OK'),
            new Status($this->user('foo', 'https://foo.example.com/2'), null, 'NG'),
            new Status($this->user('bar', 'http://bar.example.com'), null, 'OK'),
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

    public function testAllCount(): void
    {
        $action = new BadgeAction($this->Container);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/badge',
        ]));

        $response = $action($request, new Response(), []);
        $actual = $response->getHeaderLine('Location');
        $this->assertRegExp('|^https?://img\.shields\.io/badge/.*\.svg|', $actual);
        $this->assertRegExp('/3(?: |%20|\+)registered/', $actual);
    }

    public function testStatusCount(): void
    {
        $action = new BadgeAction($this->Container);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/badge',
        ]));

        $response = $action($request, new Response(), ['status' => 'OK']);
        $actual = $response->getHeaderLine('Location');
        $this->assertRegExp('|^https?://img\.shields\.io/badge/.*\.svg|', $actual);
        $this->assertRegExp('/2(?: |%20|\+)ok/', $actual);
    }

    public function testParams(): void
    {
        $action = new BadgeAction($this->Container);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/badge',
            'QUERY_STRING' => 'style=flat-square',
        ]));

        $response = $action($request, new Response(), []);
        $actual = $response->getHeaderLine('Location');
        $this->assertRegExp('|^https?://img\.shields\.io/badge/.*\.svg|', $actual);
        $this->assertStringEndsWith('?style=flat-square', $actual);
    }
}
