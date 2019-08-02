<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\ListAction;
use HomoChecker\Contracts\Service\HomoService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class ListActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        $this->users = [
            (object) [
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
            ],
            (object) [
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
            ],
            (object) [
                'screen_name' => 'bar',
                'service' => 'mastodon',
                'url' => 'http://bar.example.com',
            ],
        ];
    }

    public function testListByJSON(): void
    {
        /** @var HomoService $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('find')
             ->andReturn($this->users);

        $action = new ListAction($homo);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/list',
        ]));

        $response = $action($request, new Response(), []);
        $actual = $response->getHeaderLine('Content-Type');
        $this->assertRegExp('|^application/json|', $actual);

        $actual = (string) $response->getBody();
        $users = [
            [
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
                'display_url' => 'foo.example.com/1',
                'secure' => true,
            ],
            [
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
                'display_url' => 'foo.example.com/2',
                'secure' => true,
            ],
            [
                'screen_name' => 'bar',
                'service' => 'mastodon',
                'url' => 'http://bar.example.com',
                'display_url' => 'bar.example.com',
                'secure' => false,
            ],
        ];

        $expected = json_encode($users);
        $this->assertJsonStringEqualsJsonString($actual, $expected);
    }

    public function testListBySQL(): void
    {
        $sql = <<<'SQL'
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/1');
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/2');
        insert into "users" ("screen_name", "service", "url") values ('bar', 'twitter', 'https://bar.example.com');
        SQL;

        /** @var HomoService $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('export')
             ->andReturn($sql);

        $action = new ListAction($homo);
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_URI' => '/list',
            'QUERY_STRING' => 'format=sql',
        ]));

        $response = $action($request, new Response(), []);
        $actual = $response->getHeaderLine('Content-Type');
        $this->assertRegExp('|^application/sql|', $actual);

        $actual = (string) $response->getBody();
        $this->assertEquals($sql, $actual);
    }
}
