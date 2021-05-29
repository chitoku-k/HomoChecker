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
use HomoChecker\Contracts\Service\CacheService;
use HomoChecker\Service\Profile\TwitterProfileService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Prometheus\Counter;

class TwitterProfileServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetIconAsyncNotCached(): void
    {
        $screen_name = 'example';
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], "
                {
                    \"id\": 114514,
                    \"id_str\": \"114514\",
                    \"name\": \"test\",
                    \"screen_name\": \"test\",
                    \"profile_image_url_https\": \"{$url}\"
                }
            "),
            new Response(404, [], '
                {
                    "errors": [
                        {
                            "code": 50,
                            "message": "User not found."
                        }
                    ]
                }
            '),
            new RequestException('Connection problem occurred', new Request('GET', '')),
        ]));

        /** @var ClientInterface|MockInterface $client */
        $client = new Client(compact('handler'));

        /** @var CacheService|MockInterface $cache */
        $cache = m::mock(CacheService::class);
        $cache->shouldReceive('loadIconTwitter')
              ->andReturn(null);

        $cache->shouldReceive('saveIconTwitter')
              ->with($screen_name, $url, m::any());

        /** @var Counter|MockInterface $profileErrorCounter */
        $profileErrorCounter = m::mock(Counter::class);
        $profileErrorCounter->shouldReceive('inc')
                            ->withArgs([
                                [
                                    'service' => 'twitter',
                                    'screen_name' => $screen_name,
                                ],
                            ]);

        $profile = new TwitterProfileService($client, $cache, $profileErrorCounter);

        $this->assertEquals($url, $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
    }

    public function testGetIconAsyncCached(): void
    {
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';
        $screen_name = 'example';

        /** @var ClientInterface|MockInterface $client */
        $client = m::mock(ClientInterface::class);

        /** @var CacheService|MockInterface $cache */
        $cache = m::mock(CacheService::class);
        $cache->shouldReceive('loadIconTwitter')
              ->andReturn($url);

        /** @var Counter|MockInterface $profileErrorCounter */
        $profileErrorCounter = m::mock(Counter::class);

        $profile = new TwitterProfileService($client, $cache, $profileErrorCounter);
        $this->assertEquals($url, $profile->getIconAsync($screen_name)->wait());
    }
}
