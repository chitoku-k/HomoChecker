<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use HomoChecker\Contracts\Service\Client\Response;
use HomoChecker\Contracts\Service\ClientService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Contracts\Service\ProfileService;
use HomoChecker\Contracts\Service\ValidatorService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Result;
use HomoChecker\Domain\Status;
use HomoChecker\Domain\Validator\ValidationResult;
use HomoChecker\Service\CheckService;
use Illuminate\Support\Facades\Log;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CheckServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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
            (object) [
                'id' => 4,
                'screen_name' => 'baz',
                'service' => 'mastodon',
                'url' => 'https://baz.example.com',
            ],
        ];
    }

    public function testExecuteAsync(): void
    {
        /** @var HomoService|MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('find')
             ->with(null)
             ->andReturn($this->users);

        $twitter = m::mock(ProfileService::class);
        $twitter->shouldReceive('getIconAsync')
                ->andReturn(
                    new FulfilledPromise('https://img.example.com/foo'),
                    new FulfilledPromise('https://img.example.com/foo'),
                );

        $mastodon = m::mock(ProfileService::class);
        $mastodon->shouldReceive('getIconAsync')
                 ->andReturn(
                     new FulfilledPromise('https://img.example.com/bar'),
                     new FulfilledPromise('https://img.example.com/baz'),
                 );

        $validator = m::mock(ValidatorService::class);
        $validator->shouldReceive('validate')
                  ->andReturn(
                      ValidationResult::OK,
                      false,
                      ValidationResult::OK,
                      false,
                  );

        /** @var ClientService|MockInterface $client */
        $client = m::mock(ClientService::class);
        $client->shouldReceive('getAsync')
               ->withArgs(['https://foo.example.com/1'])
               ->andReturn(
                   (function () {
                       $response = new Response(new Psr7Response(301, ['Location' => 'https://homo.example.com'], ''));
                       $response->setTotalTime(1.0);
                       $response->setStartTransferTime(2.0);
                       $response->setPrimaryIP('2001:db8::4545:1');
                       yield 'https://foo.example.com/1' => new FulfilledPromise($response);
                   })(),
               );

        $client->shouldReceive('getAsync')
               ->withArgs(['https://foo.example.com/2'])
               ->andReturn(
                   (function () {
                       $response = new Response(new Psr7Response(301, ['Location' => 'https://foo2.example.com'], ''));
                       $response->setTotalTime(2.0);
                       $response->setStartTransferTime(3.0);
                       $response->setPrimaryIP('2001:db8::4545:2');
                       yield 'https://foo.example.com/2' => new FulfilledPromise($response);

                       $response = new Response(new Psr7Response(200, [], '
                           <!doctype html>
                           <title>Fail</title>
                       '));
                       $response->setTotalTime(3.0);
                       $response->setStartTransferTime(4.0);
                       $response->setPrimaryIP('2001:db8::4545:4');
                       yield 'https://foo2.example.com' => new FulfilledPromise($response);
                   })(),
               );

        $client->shouldReceive('getAsync')
               ->withArgs(['http://bar.example.com'])
               ->andReturn(
                   (function () {
                       $response = new Response(new Psr7Response(200, [], '
                           <!doctype html>
                           <title>Fail</title>
                       '));
                       $response->setTotalTime(3.0);
                       $response->setStartTransferTime(4.0);
                       $response->setPrimaryIP('2001:db8::4545:3');
                       yield 'http://bar.example.com' => new FulfilledPromise($response);
                   })(),
               );

        $client->shouldReceive('getAsync')
               ->withArgs(['https://baz.example.com'])
               ->andReturn(
                   (function () {
                       $exception = new RequestException('Connection error', new Psr7Request('GET', ''));
                       yield 'https://baz.example.com' => new RejectedPromise($exception);
                   })(),
               );

        Log::spy();

        $check = new CheckService($client, $homo);
        $check->setProfiles(collect(compact('twitter', 'mastodon')));
        $check->setValidators(collect([
            $validator,
        ]));

        $expected = [
            new Status([
                'homo' => new Homo([
                    'id' => 1,
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/1',
                ]),
                'result' => new Result([
                    'status' => 'OK',
                    'ip' => '2001:db8::4545:1',
                    'duration' => 2.0,
                ]),
                'icon' => 'https://img.example.com/foo',
            ]),
            new Status([
                'homo' => new Homo([
                    'id' => 2,
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                ]),
                'result' => new Result([
                    'status' => 'WRONG',
                    'ip' => '2001:db8::4545:4',
                    'duration' => 7.0,
                ]),
                'icon' => 'https://img.example.com/foo',
            ]),
            new Status([
                'homo' => new Homo([
                    'id' => 3,
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                ]),
                'result' => new Result([
                    'status' => 'OK',
                    'ip' => '2001:db8::4545:3',
                    'duration' => 4.0,
                ]),
                'icon' => 'https://img.example.com/bar',
            ]),
            new Status([
                'homo' => new Homo([
                    'id' => 4,
                    'screen_name' => 'baz',
                    'service' => 'mastodon',
                    'url' => 'https://baz.example.com',
                ]),
                'result' => new Result([
                    'status' => 'ERROR',
                    'ip' => null,
                    'duration' => 0.0,
                ]),
                'icon' => 'https://img.example.com/baz',
            ]),
        ];

        $actual = $check->execute(null, fn () => null);
        $this->assertContainsOnlyInstancesOf(Status::class, $actual);
        $this->assertEquals($expected, $actual);
    }
}
