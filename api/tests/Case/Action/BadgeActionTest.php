<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\BadgeAction;
use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class BadgeActionTest extends TestCase
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
                'status' => 'OK',
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                ]),
                'status' => 'NG',
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                ]),
                'status' => 'OK',
            ]),
        ];
    }

    public function testAllCount(): void
    {
        $check = m::mock(CheckService::class);

        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->andReturn(3);

        $action = new BadgeAction($check, $homo);
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
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->andReturn($this->statuses);

        $homo = m::mock(HomoService::class);

        $action = new BadgeAction($check, $homo);
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
        $check = m::mock(CheckService::class);

        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->andReturn(3);

        $action = new BadgeAction($check, $homo);
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
