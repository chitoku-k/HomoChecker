<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Contracts\Service\ProfileService;
use HomoChecker\Contracts\Service\ValidatorService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use HomoChecker\Domain\Validator\ValidationResult;
use HomoChecker\Service\CheckService;
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
        $client = new Client([
            'allow_redirects' => false,
            'handler' => HandlerStack::create(new MockHandler([
                // 'https://foo.example.com/1' (1/1)
                new Response(301, ['Location' => 'https://homo.example.com'], ''),
                // 'https://foo.example.com/2' (1/2)
                new Response(302, ['Location' => 'https://foo2.example.com'], ''),
                // 'http://bar.example.com' (1/1)
                new Response(200, [], '
                    <!doctype html>
                    <title>Success</title>
                    <meta http-equiv="refresh" content="0; https://homo.example.com">
                '),
                // 'https://baz.example.com' (1/1)
                new RequestException('Connection error', new Request('GET', '')),
                // 'https://foo2.example.com' (2/2)
                new Response(200, [], '
                    <!doctype html>
                    <title>Fail</title>
                '),
            ])),
        ]);

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
                'icon' => 'https://img.example.com/foo',
                'status' => 'OK',
                'ip' => null,
                'duration' => 0.0,
            ]),
            new Status([
                'homo' => new Homo([
                    'id' => 2,
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                ]),
                'icon' => 'https://img.example.com/foo',
                'status' => 'WRONG',
                'ip' => null,
                'duration' => 0.0,
            ]),
            new Status([
                'homo' => new Homo([
                    'id' => 3,
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                ]),
                'icon' => 'https://img.example.com/bar',
                'status' => 'OK',
                'ip' => null,
                'duration' => 0.0,
            ]),
            new Status([
                'homo' => new Homo([
                    'id' => 4,
                    'screen_name' => 'baz',
                    'service' => 'mastodon',
                    'url' => 'https://baz.example.com',
                ]),
                'icon' => 'https://img.example.com/baz',
                'status' => 'ERROR',
                'ip' => null,
                'duration' => 0.0,
            ]),
        ];

        $actual = $check->execute(null, fn (Status $status) => '');
        $this->assertContainsOnlyInstancesOf(Status::class, $actual);
        $this->assertEquals($expected, $actual);
    }
}
