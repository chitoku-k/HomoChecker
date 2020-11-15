<?php
declare(strict_types=1);

namespace HomoChecker\Service\Profile;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Coroutine;
use GuzzleHttp\Promise\PromiseInterface;
use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use HomoChecker\Contracts\Service\ProfileService as ProfileServiceContract;

class TwitterProfileService implements ProfileServiceContract
{
    public const CACHE_EXPIRE = 3;

    protected ClientInterface $client;
    protected CacheServiceContract $cache;

    public function __construct(ClientInterface $client, CacheServiceContract $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * Get the URL of profile image of the user.
     * @param  string           $screen_name The screen_name of the user.
     * @return PromiseInterface The promise.
     */
    public function getIconAsync(string $screen_name): PromiseInterface
    {
        return Coroutine::of(function () use ($screen_name) {
            if ($url = $this->cache->loadIconTwitter($screen_name)) {
                return yield $url;
            }

            try {
                $target = "users/show.json?screen_name={$screen_name}";
                $response = yield $this->client->getAsync($target);
                $user = json_decode((string) $response->getBody());
                $url = str_replace('_normal', '_200x200', $user->profile_image_url_https);

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
