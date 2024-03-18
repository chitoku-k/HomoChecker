<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\ListAction;
use HomoChecker\Contracts\Service\HomoService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response as HttpResponse;
use Slim\Http\ServerRequest as HttpRequest;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class ListActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected array $users;

    #[\Override]
    public function setUp(): void
    {
        $this->users = [
            (object) [
                'id' => 1,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
            ],
            (object) [
                'id' => 2,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
            ],
            (object) [
                'id' => 3,
                'screen_name' => 'bar',
                'service' => 'mastodon',
                'url' => 'http://bar.example.com',
            ],
        ];
    }

    public function testListByJSON(): void
    {
        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('find')
             ->with(null)
             ->andReturn($this->users);

        $action = new ListAction($homo);
        $request = (new RequestFactory())->createRequest('GET', '/list');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getStatusCode();
        $this->assertEquals(200, $actual);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/json|', $actual);

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
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testListWithNotFoundByJSON(): void
    {
        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('find')
             ->with('baz')
             ->andReturn([]);

        $action = new ListAction($homo);
        $request = (new RequestFactory())->createRequest('GET', '/list/baz/');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), ['name' => 'baz']);

        $actual = $response->getStatusCode();
        $this->assertEquals(404, $actual);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/json|', $actual);

        $actual = (string) $response->getBody();
        $users = [];

        $expected = json_encode($users);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testListBySQL(): void
    {
        $sql = <<<'SQL'
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/1');
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/2');
        insert into "users" ("screen_name", "service", "url") values ('bar', 'twitter', 'https://bar.example.com');
        SQL;

        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('export')
             ->andReturn($sql);

        $action = new ListAction($homo);
        $request = (new RequestFactory())->createRequest('GET', '/list?format=sql');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);
        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/sql|', $actual);

        $actual = (string) $response->getBody();
        $this->assertEquals($sql, $actual);
    }
}
