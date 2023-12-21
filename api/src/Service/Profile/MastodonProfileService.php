<?php
declare(strict_types=1);

namespace HomoChecker\Service\Profile;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Coroutine;
use GuzzleHttp\Promise\PromiseInterface;
use HomoChecker\Contracts\Repository\ProfileRepository as ProfileRepositoryContract;
use HomoChecker\Contracts\Service\ProfileService as ProfileServiceContract;
use Illuminate\Support\Facades\Log;
use Prometheus\Counter;

class MastodonProfileService implements ProfileServiceContract
{
    public const int CACHE_EXPIRE = 180;

    public function __construct(
        protected ClientInterface $client,
        protected ProfileRepositoryContract $repository,
        protected Counter $profileErrorCounter,
    ) {}

    public function parseScreenName(string $screen_name): array
    {
        if (!preg_match('/\A@?(?<username>[^@]+)@(?<instance>.*)\/*/', $screen_name, $matches)) {
            throw new \RuntimeException('Unexpected format.');
        }

        return [
            $matches['username'],
            $matches['instance'],
        ];
    }

    /**
     * Get the URL of profile image of the user.
     * @param  string           $screen_name The screen_name of the user, e.g., @example@mastodon.social
     * @return PromiseInterface The promise.
     */
    #[\Override]
    public function getIconAsync(string $screen_name): PromiseInterface
    {
        return Coroutine::of(function () use ($screen_name) {
            try {
                [$username, $instance] = $this->parseScreenName($screen_name);
                $target = "https://{$instance}/users/{$username}.json";
                $response = yield $this->client->getAsync($target);
                $body = json_decode((string) $response->getBody());
                $url = $body->icon->url ?? null;
                if (!$url) {
                    throw new \RuntimeException('Avatar not found');
                }

                $this->repository->save(
                    $screen_name,
                    $url,
                    (new \DateTimeImmutable(static::CACHE_EXPIRE . ' seconds'))->format(\DateTimeInterface::ATOM),
                );
                return yield $url;
            } catch (\Throwable $e) {
                Log::debug($e);

                $this->profileErrorCounter->inc([
                    'service' => 'mastodon',
                    'screen_name' => $screen_name,
                ]);

                return yield $this->getDefaultUrl();
            }
        });
    }

    #[\Override]
    public function getDefaultUrl(): string
    {
        return 'https://mastodon.social/avatars/original/missing.png';
    }
}
