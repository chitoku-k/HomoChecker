<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\BadgeAction;
use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Result;
use HomoChecker\Domain\Status;
use HomoChecker\Domain\Validator\ValidationResult;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response as HttpResponse;
use Slim\Http\ServerRequest as HttpRequest;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class BadgeActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected array $users;
    protected array $statuses;

    #[\Override]
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
                    'status' => ValidationResult::OK,
                    'url' => 'https://foo.example.com/1',
                ]),
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                ]),
                'result' => new Result([
                    'status' => ValidationResult::WRONG,
                    'url' => 'https://foo.example.com/2',
                ]),
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                ]),
                'result' => new Result([
                    'status' => ValidationResult::OK,
                    'url' => 'http://bar.example.com',
                ]),
            ]),
        ];
    }

    public function testAllCount(): void
    {
        /** @var CheckService&MockInterface $check */
        $check = m::mock(CheckService::class);

        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->andReturn(3);

        $action = new BadgeAction($check, $homo);
        $request = (new RequestFactory())->createRequest('GET', '/badge');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);
        $actual = $response->getHeaderLine('Location');
        $this->assertMatchesRegularExpression('|^https?://img\.shields\.io/badge/.*\.svg|', $actual);
        $this->assertMatchesRegularExpression('/3(?: |%20|\+)registered/', $actual);
    }

    public function testStatusCount(): void
    {
        /** @var CheckService&MockInterface $check */
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->andReturn($this->statuses);

        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);

        $action = new BadgeAction($check, $homo);
        $request = (new RequestFactory())->createRequest('GET', '/badge');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), ['status' => 'OK']);
        $actual = $response->getHeaderLine('Location');
        $this->assertMatchesRegularExpression('|^https?://img\.shields\.io/badge/.*\.svg|', $actual);
        $this->assertMatchesRegularExpression('/2(?: |%20|\+)ok/', $actual);
    }

    public function testParams(): void
    {
        /** @var CheckService&MockInterface $check */
        $check = m::mock(CheckService::class);

        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->andReturn(3);

        $action = new BadgeAction($check, $homo);
        $request = (new RequestFactory())->createRequest('GET', '/badge?style=flat-square');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);
        $actual = $response->getHeaderLine('Location');
        $this->assertMatchesRegularExpression('|^https?://img\.shields\.io/badge/.*\.svg|', $actual);
        $this->assertStringEndsWith('?style=flat-square', $actual);
    }
}
