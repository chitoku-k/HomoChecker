<?php
declare(strict_types=1);

namespace HomoChecker\Model\Profile;

use GuzzleHttp\Promise;
use GuzzleHttp\ClientInterface;

class Icon implements ProfileInterface
{
    public const CACHE_EXPIRE = 300;
    public static $default = 'https://abs.twimg.com/sticky/default_profile_images/default_profile_200x200.png';

    public function __construct(ClientInterface $client, \Redis $redis)
    {
        $this->client = $client;
        $this->redis = $redis;
    }

    /**
     * Get the URL of profile image of the user.
     * @param  string                   $screen_name The screen_name of the user.
     * @return Promise\PromiseInterface The promise.
     */
    public function getAsync(string $screen_name): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($screen_name) {
            if ($url = $this->redis->get("icon:twitter:{$screen_name}")) {
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

                $this->save($screen_name, $url);
            } catch (\RuntimeException $e) {
                $url = static::$default;
            }

            return yield $url ?? static::$default;
        });
    }

    protected function save(string $screen_name, string $url)
    {
        return $this->redis->set("icon:twitter:{$screen_name}", $url, static::CACHE_EXPIRE);
    }
}
