<?php
declare(strict_types=1);

namespace HomoChecker\Test\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HomoChecker\Model\Cache;
use HomoChecker\Model\Profile\TwitterProfile;
use PHPUnit\Framework\TestCase;

class TwitterProfileTest extends TestCase
{
    public function testGetIconAsync(): void
    {
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

        $client = new Client(compact('handler'));
        $redis = $this->getMockBuilder(\Redis::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache = new Cache($redis);

        $profile = new TwitterProfile($client, $cache);
        $this->assertEquals($url, $profile->getIconAsync('example')->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('example')->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('example')->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('example')->wait());
    }

    public function testGetServiceName(): void
    {
        $handler = HandlerStack::create(new MockHandler([]));
        $client = new Client(compact('handler'));
        $redis = $this->getMockBuilder(\Redis::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache = new Cache($redis);

        $profile = new TwitterProfile($client, $cache);
        $this->assertEquals('twitter', $profile->getServiceName());
    }
}
