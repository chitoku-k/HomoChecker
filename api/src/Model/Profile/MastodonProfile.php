<?php
declare(strict_types=1);

namespace HomoChecker\Model\Profile;

use GuzzleHttp\Promise;
use GuzzleHttp\ClientInterface;

class MastodonProfile extends Profile
{
    public function parseScreenName(string $screen_name): array
    {
        if (!preg_match('/@?(?<username>[^@]+)@(?<instance>.*)\/*/', $screen_name, $matches)) {
            throw new \RuntimeException('Unexpected format.');
        }

        return [
            $matches['username'],
            $matches['instance'],
        ];
    }

    /**
     * Get the URL of profile image of the user.
     * @param  string                   $screen_name The screen_name of the user, e.g., @example@mastodon.social
     * @return Promise\PromiseInterface The promise.
     */
    public function getIconAsync(string $screen_name): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($screen_name) {
            if ($url = $this->cache->loadMastodonTwitter($screen_name)) {
                return yield $url;
            }

            try {
                [ $username, $instance ] = $this->parseScreenName($screen_name);
                $target = "https://{$instance}/users/{$username}.json";
                $response = yield $this->client->getAsync($target);
                $body = json_decode((string)$response->getBody());
                $url = $body->icon->url;
                if (!$url) {
                    throw new \RuntimeException('Avatar not found');
                }

                $this->cache->saveIconMastodon($screen_name, $url, static::CACHE_EXPIRE);
                return yield $url;
            } catch (\RuntimeException $e) {
                return yield $this->getDefaultUrl();
            }
        });
    }

    public function getDefaultUrl(): string
    {
        return 'https://mastodon.social/avatars/original/missing.png';
    }

    public function getServiceName(): string
    {
        return 'mastodon';
    }
}

