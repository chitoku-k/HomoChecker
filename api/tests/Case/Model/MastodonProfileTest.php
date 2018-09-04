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
use HomoChecker\Model\Profile\MastodonProfile;
use PHPUnit\Framework\TestCase;

class MastodonProfileTest extends TestCase
{
    public function testGetIconAsync(): void
    {
        $url = 'https://files.mastodon.social/accounts/avatars/000/000/001/original/114514.png';
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], "<?xml version=\"1.0\"?>
                <feed xmlns=\"http://www.w3.org/2005/Atom\" xmlns:media=\"http://purl.org/syndication/atommedia\">
                    <title>This contains icon URL</title>
                    <author>
                        <link rel=\"avatar\" type=\"image/png\" media:width=\"120\" media:height=\"120\" href=\"{$url}\"/>
                    </author>
                </feed>
            "),
            new Response(200, [], "<?xml version=\"1.0\"?>
                <feed xmlns=\"http://www.w3.org/2005/Atom\" xmlns:media=\"http://purl.org/syndication/atommedia\">
                    <title>This does not contain icon URL</title>
                    <author>
                    </author>
                </feed>
            "),
            new Response(404, [], "<?xml version=\"1.0\"?>
                <feed xmlns=\"http://www.w3.org/2005/Atom\" xmlns:media=\"http://purl.org/syndication/atommedia\">
                    <title>This returns 404</title>
                    <author>
                        <link rel=\"avatar\" type=\"image/png\" media:width=\"120\" media:height=\"120\" href=\"{$url}\"/>
                    </author>
                </feed>
            "),
            new RequestException('Connection problem occurred', new Request('GET', '')),
        ]));

        $client = new Client(compact('handler'));
        $redis = $this->getMockBuilder(\Redis::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache = new Cache($redis);

        $profile = new MastodonProfile($client, $cache);
        $this->assertEquals($url, $profile->getIconAsync('@example@mastodon.social')->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('@example@mastodon.social')->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('@example@mastodon.social')->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('@example@mastodon.social')->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('example@wrong-format.example.com')->wait());
    }

    public function testGetServiceName(): void
    {
        $handler = HandlerStack::create(new MockHandler([]));
        $client = new Client(compact('handler'));
        $redis = $this->getMockBuilder(\Redis::class)
                      ->disableOriginalConstructor()
                      ->getMock();
        $cache = new Cache($redis);

        $profile = new MastodonProfile($client, $cache);
        $this->assertEquals('mastodon', $profile->getServiceName());
    }
}
