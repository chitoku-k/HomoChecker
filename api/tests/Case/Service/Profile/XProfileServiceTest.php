<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service\Profile;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HomoChecker\Contracts\Repository\ProfileRepository;
use HomoChecker\Service\Profile\XProfileService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Prometheus\Counter;

class XProfileServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetIconAsync(): void
    {
        $screen_name = 'example';
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{}'),
            new Response(200, [], '
                {
                    "guest_token": "1145141919"
                }
            '),
            new Response(200, [], '
                {
                    "data": {
                        "user": {
                            "result": {
                                "__typename": "User",
                                "id": "VXNlcjoxMTQ1MTQ=",
                                "rest_id": "114514",
                                "legacy": {
                                    "name": "test",
                                    "screen_name": "test",
                                    "profile_image_url_https": "https://pbs.twimg.com/profile_images/114514/example_bigger.jpg"
                                }
                            }
                        }
                    }
                }
            '),
            new Response(200, [], '{"data": {}}'),
            new RequestException('Connection problem occurred', new Request('GET', '')),
        ]));

        /** @var ClientInterface&MockInterface $client */
        $client = new Client(compact('handler'));

        /** @var MockInterface&ProfileRepository $repository */
        $repository = m::mock(ProfileRepository::class);
        $repository->shouldReceive('save')
                   ->withArgs([$screen_name, $url, m::type('string')]);

        /** @var Counter&MockInterface $profileErrorCounter */
        $profileErrorCounter = m::mock(Counter::class);
        $profileErrorCounter->shouldReceive('inc')
                            ->withArgs([
                                [
                                    'service' => 'twitter',
                                    'screen_name' => $screen_name,
                                ],
                            ]);

        $profile = new XProfileService($client, $repository, $profileErrorCounter);

        // (1) Retrieving guest_token fails
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());

        // (2) Retrieving guest_token succeeds and user is retrieved
        $this->assertEquals($url, $profile->getIconAsync($screen_name)->wait());

        // (3) Using the cached guest_token and retrieved user is empty
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());

        // (4) Using the cached guest_token and retrieving user fails
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
    }
}
