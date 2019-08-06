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
use HomoChecker\Service\Profile\MastodonProfileService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class MastodonProfileServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetIconAsyncNotCached(): void
    {
        $screen_name = '@example@mastodon.social';
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
            new Response(200, [], '
                {
                    "name": "This does not contain icon URL"
                }
            '),
            new Response(404, [], ''),
            new RequestException('Connection problem occurred', new Request('GET', '')),
        ]));

        $client = new Client(compact('handler'));

        /** @var CacheService|MockInterface $cache */
        $cache = m::mock(CacheService::class);
        $cache->shouldReceive('loadIconMastodon')
              ->andReturn(null);

        $cache->shouldReceive('saveIconMastodon')
              ->with($screen_name, $url, m::any());

        $profile = new MastodonProfileService($client, $cache);

        $this->assertEquals($url, $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync($screen_name)->wait());
        $this->assertEquals($profile->getDefaultUrl(), $profile->getIconAsync('example@wrong-format.example.com')->wait());
    }

    public function testGetIconAsyncCached(): void
    {
        $url = 'https://files.mastodon.social/accounts/avatars/000/000/001/original/114514.png';
        $screen_name = '@example@mastodon.social';

        /** @var ClientInterface|MockInterface $client */
        $client = m::mock(ClientInterface::class);

        /** @var CacheService|MockInterface $cache */
        $cache = m::mock(CacheService::class);
        $cache->shouldReceive('loadIconMastodon')
              ->andReturn($url);

        $profile = new MastodonProfileService($client, $cache);
        $this->assertEquals($url, $profile->getIconAsync($screen_name)->wait());
    }

    /**
     * @dataProvider screenNameProvider
     */
    public function testParseScreenName($screen_name, $username, $instance): void
    {
        /** @var ClientInterface|MockInterface $client */
        $client = m::mock(ClientInterface::class);

        /** @var CacheService|MockInterface $cache */
        $cache = m::mock(CacheService::class);

        $profile = new MastodonProfileService($client, $cache);

        $actual = $profile->parseScreenName($screen_name);
        $this->assertEquals([$username, $instance], $actual);
    }

    /**
     * @dataProvider invalidScreenNameProvider
     */
    public function testParseScreenNameInvalid($screen_name): void
    {
        $this->expectException(\RuntimeException::class);

        /** @var ClientInterface|MockInterface $client */
        $client = m::mock(ClientInterface::class);

        /** @var CacheService|MockInterface $cache */
        $cache = m::mock(CacheService::class);

        $profile = new MastodonProfileService($client, $cache);
        $profile->parseScreenName($screen_name);
    }

    public function screenNameProvider(): array
    {
        return [
            'start with @' => [
                '@example@mastodon.social',
                'example',
                'mastodon.social',
            ],
            'start not with @' => [
                'example@mastodon.social',
                'example',
                'mastodon.social',
            ],
        ];
    }

    public function invalidScreenNameProvider(): array
    {
        return [
            'start with @@' => [
                '@@example@mastodon.social',
            ],
            'username only' => [
                'example',
            ],
            'username only with @' => [
                '@example',
            ],
            'instance name only' => [
                'mastodon.social',
            ],
            'instance name only with @' => [
                '@mastodon.social',
            ],
        ];
    }
}
