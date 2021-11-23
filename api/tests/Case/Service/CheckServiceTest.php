<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service;

use GuzzleHttp\Exception\InvalidArgumentException;
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
use Prometheus\Counter;

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
            (object) [
                'id' => 5,
                'screen_name' => 'qux',
                'service' => 'mastodon',
                'url' => 'https://qux.example.com',
            ],
            (object) [
                'id' => 6,
                'screen_name' => 'quux',
                'service' => 'mastodon',
                'url' => '',
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
                     new FulfilledPromise('https://img.example.com/qux'),
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
                       $response->setHttpVersion(CURL_HTTP_VERSION_2);
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
                       $response->setHttpVersion(CURL_HTTP_VERSION_1_1);
                       $response->setPrimaryIP('2001:db8::4545:2');
                       yield 'https://foo.example.com/2' => new FulfilledPromise($response);

                       $response = new Response(new Psr7Response(200, [], '
                           <!doctype html>
                           <title>Fail</title>
                       '));
                       $response->setTotalTime(3.0);
                       $response->setStartTransferTime(4.0);
                       $response->setHttpVersion(CURL_HTTP_VERSION_1_1);
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
                       $response->setHttpVersion(null);
                       $response->setPrimaryIP('2001:db8::4545:3');
                       yield 'http://bar.example.com' => new FulfilledPromise($response);
                   })(),
               );

        $client->shouldReceive('getAsync')
               ->withArgs(['https://baz.example.com'])
               ->andReturn(
                   (function () {
                       $exception = new RequestException('Connection error', new Psr7Request('GET', ''), null, null, [
                           'error' => 'Resolving timed out after 5000 milliseconds',
                       ]);
                       yield 'https://baz.example.com' => new RejectedPromise($exception);
                   })(),
               );

        $client->shouldReceive('getAsync')
               ->withArgs(['https://qux.example.com'])
               ->andReturn(
                   (function () {
                       $exception = new InvalidArgumentException('Empty host provided');
                       yield 'https://qux.example.com' => new RejectedPromise($exception);
                   })(),
               );

        /** @var Counter|MockInterface $checkCounter */
        $checkCounter = m::mock(Counter::class);
        $checkCounter->shouldReceive('inc')
                     ->withArgs([
                         [
                             'status' => 'OK',
                             'code' => 301,
                             'screen_name' => 'foo',
                             'url' => 'https://foo.example.com/1',
                         ],
                     ]);

        $checkCounter->shouldReceive('inc')
                     ->withArgs([
                         [
                             'status' => 'WRONG',
                             'code' => 200,
                             'screen_name' => 'foo',
                             'url' => 'https://foo.example.com/2',
                         ],
                     ]);

        $checkCounter->shouldReceive('inc')
                     ->withArgs([
                         [
                             'status' => 'OK',
                             'code' => 200,
                             'screen_name' => 'bar',
                             'url' => 'http://bar.example.com',
                         ],
                     ]);

        $checkCounter->shouldReceive('inc')
                     ->withArgs([
                         [
                             'status' => 'ERROR',
                             'code' => 0,
                             'screen_name' => 'baz',
                             'url' => 'https://baz.example.com',
                         ],
                     ]);

        $checkCounter->shouldReceive('inc')
                     ->withArgs([
                         [
                             'status' => 'ERROR',
                             'code' => 0,
                             'screen_name' => 'qux',
                             'url' => 'https://qux.example.com',
                         ],
                     ]);

        /** @var Counter|MockInterface $checkErrorCounter */
        $checkErrorCounter = m::mock(Counter::class);
        $checkErrorCounter->shouldReceive('inc')
                          ->withArgs([
                              [
                                  'status' => 'ERROR',
                                  'code' => 0,
                                  'screen_name' => 'baz',
                                  'url' => 'https://baz.example.com',
                              ],
                          ]);

        $checkErrorCounter->shouldReceive('inc')
                          ->withArgs([
                              [
                                  'status' => 'ERROR',
                                  'code' => 0,
                                  'screen_name' => 'qux',
                                  'url' => 'https://qux.example.com',
                              ],
                          ]);

        Log::spy();

        $profiles = collect(compact('twitter', 'mastodon'));
        $validators = collect([$validator]);

        $check = new CheckService($client, $homo, $checkCounter, $checkErrorCounter, $profiles, $validators);

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
                    'code' => '301 Moved Permanently',
                    'http' => '2',
                    'ip' => '2001:db8::4545:1',
                    'url' => 'https://foo.example.com/1',
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
                    'code' => '200 OK',
                    'http' => '1.1',
                    'ip' => '2001:db8::4545:4',
                    'url' => 'https://foo2.example.com',
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
                    'code' => '200 OK',
                    'ip' => '2001:db8::4545:3',
                    'url' => 'http://bar.example.com',
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
                    'http' => null,
                    'url' => 'https://baz.example.com',
                    'duration' => 0.0,
                    'error' => 'Resolving timed out after 5000 milliseconds',
                ]),
                'icon' => 'https://img.example.com/baz',
            ]),
            new Status([
                'homo' => new Homo([
                    'id' => 5,
                    'screen_name' => 'qux',
                    'service' => 'mastodon',
                    'url' => 'https://qux.example.com',
                ]),
                'result' => new Result([
                    'status' => 'ERROR',
                    'ip' => null,
                    'http' => null,
                    'url' => 'https://qux.example.com',
                    'duration' => 0.0,
                ]),
                'icon' => 'https://img.example.com/qux',
            ]),
            new \InvalidArgumentException('Invalid URL'),
        ];

        $actual = $check->execute(null, fn () => null);
        $this->assertEquals($expected, $actual);
    }
}
