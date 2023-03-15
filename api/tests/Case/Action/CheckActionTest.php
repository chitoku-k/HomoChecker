<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\CheckAction;
use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Result;
use HomoChecker\Domain\Status;
use HomoChecker\Domain\Validator\ValidationResult;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Response as HttpResponse;
use Slim\Http\ServerRequest as HttpRequest;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class CheckActionTest extends TestCase
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
                'result' => new Result([
                    'status' => ValidationResult::OK,
                    'code' => '302 Found',
                    'http' => '2.0',
                    'certificates' => [
                        [
                            'subject' => 'CN = foo.example.com',
                            'issuer' => 'C = US, O = Let\'s Encrypt, CN = E1',
                            'subjectAlternativeName' => ['foo.example.com'],
                            'notBefore' => 'Aug  1 00:00:00 2022 GMT',
                            'notAfter' => 'Aug 31 23:59:59 2022 GMT',
                        ],
                    ],
                    'ip' => '2001:db8::4545:1',
                    'url' => 'https://foo.example.com/1',
                    'secure' => true,
                    'duration' => 10,
                ]),
                'icon' => 'https://icon.example.com/1',
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                ]),
                'result' => new Result([
                    'status' => ValidationResult::WRONG,
                    'code' => '404 Not Found',
                    'http' => '1.1',
                    'certificates' => [
                        [
                            'subject' => 'CN = foo.example.com',
                            'issuer' => 'C = US, O = Let\'s Encrypt, CN = E1',
                            'subjectAlternativeName' => ['foo.example.com'],
                            'notBefore' => 'Aug  1 00:00:00 2022 GMT',
                            'notAfter' => 'Aug 31 23:59:59 2022 GMT',
                        ],
                    ],
                    'ip' => '2001:db8::4545:2',
                    'url' => 'https://foo.example.com/2',
                    'secure' => true,
                    'duration' => 20,
                ]),
                'icon' => 'https://icon.example.com/2',
            ]),
            new Status([
                'homo' => new Homo([
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                ]),
                'result' => new Result([
                    'status' => ValidationResult::OK,
                    'code' => '200 OK',
                    'http' => '1.1',
                    'certificates' => [
                        [
                            'subject' => 'CN = bar.example.com',
                            'issuer' => 'C = US, O = Amazon, OU = Server CA 1B, CN = Amazon',
                            'subjectAlternativeName' => [
                                '*.bar.example.com',
                                'bar.example.com',
                            ],
                            'notBefore' => 'Jul  1 00:00:00 2022 GMT',
                            'notAfter' => 'Jul 30 23:59:59 2023 GMT',
                        ],
                    ],
                    'ip' => '2001:db8::4545:3',
                    'url' => 'https://bar.example.com/2',
                    'secure' => true,
                    'duration' => 30,
                ]),
                'icon' => 'https://icon.example.com/3',
            ]),
        ];
    }

    public function testRouteToJSON(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/check?format=json');

        /** @var CheckService&MockInterface $check */
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->with(null)
              ->andReturn($this->statuses);

        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);

        /** @var StreamInterface $stream */
        $stream = m::mock(StreamInterface::class);

        $action = new CheckAction($check, $homo, $stream);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/json|', $actual);

        $actual = $response->getHeaderLine('Cache-Control');
        $this->assertEquals('no-store', $actual);

        $actual = (string) $response->getBody();
        $expected = json_encode([
            [
                'homo' => [
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/1',
                    'display_url' => 'foo.example.com/1',
                    'secure' => true,
                    'icon' => 'https://icon.example.com/1',
                ],
                'status' => 'OK',
                'code' => '302 Found',
                'http' => '2.0',
                'certificates' => [
                    [
                        'subject' => 'CN = foo.example.com',
                        'issuer' => 'C = US, O = Let\'s Encrypt, CN = E1',
                        'subjectAlternativeName' => ['foo.example.com'],
                        'notBefore' => 'Aug  1 00:00:00 2022 GMT',
                        'notAfter' => 'Aug 31 23:59:59 2022 GMT',
                    ],
                ],
                'ip' => '2001:db8::4545:1',
                'url' => 'https://foo.example.com/1',
                'secure' => true,
                'duration' => 10,
                'error' => null,
            ],
            [
                'homo' => [
                    'screen_name' => 'foo',
                    'service' => 'twitter',
                    'url' => 'https://foo.example.com/2',
                    'display_url' => 'foo.example.com/2',
                    'secure' => true,
                    'icon' => 'https://icon.example.com/2',
                ],
                'status' => 'WRONG',
                'code' => '404 Not Found',
                'http' => '1.1',
                'certificates' => [
                    [
                        'subject' => 'CN = foo.example.com',
                        'issuer' => 'C = US, O = Let\'s Encrypt, CN = E1',
                        'subjectAlternativeName' => ['foo.example.com'],
                        'notBefore' => 'Aug  1 00:00:00 2022 GMT',
                        'notAfter' => 'Aug 31 23:59:59 2022 GMT',
                    ],
                ],
                'ip' => '2001:db8::4545:2',
                'url' => 'https://foo.example.com/2',
                'secure' => true,
                'duration' => 20,
                'error' => null,
            ],
            [
                'homo' => [
                    'screen_name' => 'bar',
                    'service' => 'mastodon',
                    'url' => 'http://bar.example.com',
                    'display_url' => 'bar.example.com',
                    'secure' => false,
                    'icon' => 'https://icon.example.com/3',
                ],
                'status' => 'OK',
                'code' => '200 OK',
                'http' => '1.1',
                'certificates' => [
                    [
                        'subject' => 'CN = bar.example.com',
                        'issuer' => 'C = US, O = Amazon, OU = Server CA 1B, CN = Amazon',
                        'subjectAlternativeName' => [
                            '*.bar.example.com',
                            'bar.example.com',
                        ],
                        'notBefore' => 'Jul  1 00:00:00 2022 GMT',
                        'notAfter' => 'Jul 30 23:59:59 2023 GMT',
                    ],
                ],
                'ip' => '2001:db8::4545:3',
                'url' => 'https://bar.example.com/2',
                'secure' => true,
                'duration' => 30,
                'error' => null,
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    #[DataProvider('formatProvider')]
    public function testRouteToSSE(mixed $format = null): void
    {
        $request = (new RequestFactory())->createRequest('GET', "/check?format={$format}");

        /** @var HomoService&MockInterface $homo */
        $homo = m::mock(HomoService::class);
        $homo->shouldReceive('count')
             ->with(null)
             ->andReturn(3);

        /** @var MockInterface&StreamInterface $stream */
        $stream = m::mock(StreamInterface::class);
        $stream->shouldReceive('write')
               ->once()
               ->with("event: initialize\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"count\":3}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"foo\",\"service\":\"twitter\",\"url\":\"https:\\/\\/foo.example.com\\/1\",\"display_url\":\"foo.example.com\\/1\",\"secure\":true,\"icon\":\"https:\\/\\/icon.example.com\\/1\"},\"status\":\"OK\",\"code\":\"302 Found\",\"http\":\"2.0\",\"certificates\":[{\"subject\":\"CN = foo.example.com\",\"issuer\":\"C = US, O = Let's Encrypt, CN = E1\",\"subjectAlternativeName\":[\"foo.example.com\"],\"notBefore\":\"Aug  1 00:00:00 2022 GMT\",\"notAfter\":\"Aug 31 23:59:59 2022 GMT\"}],\"ip\":\"2001:db8::4545:1\",\"url\":\"https:\\/\\/foo.example.com\\/1\",\"secure\":true,\"duration\":10,\"error\":null}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"foo\",\"service\":\"twitter\",\"url\":\"https:\\/\\/foo.example.com\\/2\",\"display_url\":\"foo.example.com\\/2\",\"secure\":true,\"icon\":\"https:\\/\\/icon.example.com\\/2\"},\"status\":\"WRONG\",\"code\":\"404 Not Found\",\"http\":\"1.1\",\"certificates\":[{\"subject\":\"CN = foo.example.com\",\"issuer\":\"C = US, O = Let's Encrypt, CN = E1\",\"subjectAlternativeName\":[\"foo.example.com\"],\"notBefore\":\"Aug  1 00:00:00 2022 GMT\",\"notAfter\":\"Aug 31 23:59:59 2022 GMT\"}],\"ip\":\"2001:db8::4545:2\",\"url\":\"https:\\/\\/foo.example.com\\/2\",\"secure\":true,\"duration\":20,\"error\":null}\n\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("event: response\n");

        $stream->shouldReceive('write')
               ->once()
               ->with("data: {\"homo\":{\"screen_name\":\"bar\",\"service\":\"mastodon\",\"url\":\"http:\\/\\/bar.example.com\",\"display_url\":\"bar.example.com\",\"secure\":false,\"icon\":\"https:\\/\\/icon.example.com\\/3\"},\"status\":\"OK\",\"code\":\"200 OK\",\"http\":\"1.1\",\"certificates\":[{\"subject\":\"CN = bar.example.com\",\"issuer\":\"C = US, O = Amazon, OU = Server CA 1B, CN = Amazon\",\"subjectAlternativeName\":[\"*.bar.example.com\",\"bar.example.com\"],\"notBefore\":\"Jul  1 00:00:00 2022 GMT\",\"notAfter\":\"Jul 30 23:59:59 2023 GMT\"}],\"ip\":\"2001:db8::4545:3\",\"url\":\"https:\\/\\/bar.example.com\\/2\",\"secure\":true,\"duration\":30,\"error\":null}\n\n");

        /** @var CheckService&MockInterface $check */
        $check = m::mock(CheckService::class);
        $check->shouldReceive('execute')
              ->withArgs(function (?string $screen_name, callable $callback) {
                  $callback($this->statuses[0]);
                  $callback($this->statuses[1]);
                  $callback($this->statuses[2]);
                  return true;
              });

        $action = new CheckAction($check, $homo, $stream);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertEquals('text/event-stream', $actual);

        $actual = $response->getHeaderLine('Cache-Control');
        $this->assertEquals('no-store', $actual);
    }

    public static function formatProvider()
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
