<?php
declare(strict_types=1);

namespace HomoChecker\Service\Profile;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise;
use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use HomoChecker\Contracts\Service\ProfileService as ProfileServiceContract;

class TwitterProfileService implements ProfileServiceContract
{
    public const CACHE_EXPIRE = 300;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var CacheServiceContract
     */
    protected $cache;

    public function __construct(ClientInterface $client, CacheServiceContract $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * Get the URL of profile image of the user.
     * @param  string                   $screen_name The screen_name of the user.
     * @return Promise\PromiseInterface The promise.
     */
    public function getIconAsync(string $screen_name): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($screen_name) {
            if ($url = $this->cache->loadIconTwitter($screen_name)) {
                return yield $url;
            }

            try {
                $target = "https://twitter.com/intent/user?screen_name={$screen_name}";
                $response = yield $this->client->getAsync($target);
                $body = (string)$response->getBody();

                if (!preg_match('/src=(?:\"|\')(https:\/\/[ap]bs\.twimg\.com\/[^\"\']+)/', $body, $matches)) {
                    throw new \RuntimeException('No URL found');
                }
                [, $url] = $matches;

                $this->cache->saveIconTwitter($screen_name, $url, static::CACHE_EXPIRE);
                return yield $url;
            } catch (\RuntimeException $e) {
                return yield $this->getDefaultUrl();
            }
        });
    }

    public function getDefaultUrl(): string
    {
        return 'https://abs.twimg.com/sticky/default_profile_images/default_profile_200x200.png';
    }
}
