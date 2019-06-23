<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use HomoChecker\Service\CacheService;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;

class CacheServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testLoadIconMastodon(): void
    {
        $screen_name = '@example@mastodon.social';
        $url = 'https://files.mastodon.social/accounts/avatars/000/000/001/original/114514.png';

        Cache::shouldReceive('get')
             ->once()
             ->with('icon:mastodon:@example@mastodon.social')
             ->andReturn($url);

        $cache = new CacheService();

        $this->assertEquals($url, $cache->loadIconMastodon($screen_name));
    }

    public function testLoadIconTwitter(): void
    {
        $screen_name = 'example';
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';

        Cache::shouldReceive('get')
             ->once()
             ->with('icon:twitter:example')
             ->andReturn($url);

        $cache = new CacheService();

        $this->assertEquals($url, $cache->loadIconTwitter($screen_name));
    }

    public function testSaveIconMastodon(): void
    {
        $screen_name = '@example@mastodon.social';
        $url = 'https://files.mastodon.social/accounts/avatars/000/000/001/original/114514.png';

        Cache::shouldReceive('put')
             ->once()
             ->with('icon:mastodon:@example@mastodon.social', $url);

        $cache = new CacheService();
        $cache->saveIconMastodon($screen_name, $url);

        $this->assertTrue(true);
    }

    public function testSaveIconTwitter(): void
    {
        $screen_name = 'example';
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';

        Cache::shouldReceive('put')
             ->once()
             ->with('icon:twitter:example', $url);

        $cache = new CacheService();
        $cache->saveIconTwitter($screen_name, $url);

        $this->assertTrue(true);
    }

    public function testInvalidCall(): void
    {
        $this->expectException(\LogicException::class);

        $cache = new CacheService();
        $cache->unsupportedMethod();
    }
}
