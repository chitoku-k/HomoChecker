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
use PHPUnit\Framework\TestCase;

class TwitterProfileServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetIconAsyncNotCached(): void
    {
        $screen_name = 'example';
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], "
                <!doctype html>
                <html>
                    <head>
                        <title>This contains icon URL</title>
                    </head>
                    <body>
                        <img src='{$url}'>
                    </body>
                </html>
            "),
            new Response(200, [], '
                <!doctype html>
                <html>
                    <head>
                        <title>This does not contain icon URL</title>
                    </head>
                    <body>
                    </body>
                </html>
            '),
            new Response(404, [], "
                <!doctype html>
                <html>
                    <head>
                        <title>This returns 404</title>
                    </head>
                    <body>
                        <img src='{$url}'>
                    </body>
                </html>
            "),
            new RequestException('Connection problem occurred', new Request('GET', '')),
        ]));

        /** @var ClientInterface $client */
        $client = new Client(compact('handler'));

        /** @var CacheService $cache */
        $cache = m::mock(CacheService::class);
        $cache->shouldReceive('loadIconTwitter')
              ->andReturn(null);

        $cache->shouldReceive('saveIconTwitter')
              ->with($screen_name, $url, m::any());

        $profile = new TwitterProfileService($client, $cache);

        $this->assertEquals($url, $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
    }

    public function testGetIconAsyncCached(): void
    {
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';
        $screen_name = 'example';

        /** @var ClientInterface $client */
        $client = m::mock(ClientInterface::class);

        /** @var CacheService $cache */
        $cache = m::mock(CacheService::class);
        $cache->shouldReceive('loadIconTwitter')
              ->andReturn($url);

        $profile = new TwitterProfileService($client, $cache);
        $this->assertEquals($url, $profile->getIconAsync($screen_name)->wait());
    }
}
