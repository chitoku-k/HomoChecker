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
            new Response(200, [], "
                {
                    \"name\": \"This contains icon URL\",
                    \"icon\": {
                        \"type\": \"Image\",
                        \"mediaType\": \"image/png\",
                        \"url\": \"{$url}\"
                    }
                }
            "),
            new Response(200, [], "
                {
                    \"name\": \"This does not contain icon URL\",
                }
            "),
            new Response(404, [], ""),
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
