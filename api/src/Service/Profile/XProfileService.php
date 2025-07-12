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

final class XProfileService implements ProfileServiceContract
{
    public const int CACHE_EXPIRE = 180;

    public const string X_API_GRAPHQL_ROOT = 'https://x.com/i/api/graphql/sLVLhk0bGj3MVFEKTdax1w/';
    public const string TOKEN = 'Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA';

    private ?string $guestToken = null;

    public function __construct(
        private ClientInterface $client,
        private ProfileRepositoryContract $repository,
        private Counter $profileErrorCounter,
    ) {}

    private function getGuestToken(): PromiseInterface
    {
        return Coroutine::of(function () {
            if ($this->guestToken) {
                return yield $this->guestToken;
            }

            $target = 'guest/activate.json';
            $response = yield $this->client->postAsync($target, [
                'headers' => [
                    'Authorization' => static::TOKEN,
                ],
            ]);
            $guest = json_decode((string) $response->getBody());
            if (!isset($guest->guest_token) || !is_string($guest->guest_token)) {
                throw new \RuntimeException('Error issuing guest_token');
            }

            $this->guestToken = $guest->guest_token;
            return yield $this->guestToken;
        });
    }

    private function generateHeaders(): PromiseInterface
    {
        return Coroutine::of(function () {
            /** @var string $guestToken */
            $guestToken = yield $this->getGuestToken();
            $csrfToken = uniqid();
            $cookie = implode('; ', [
                'guest_id=' . urlencode("v1:{$guestToken}"),
                'ct0=' . urlencode($csrfToken),
            ]);

            return yield [
                'Accept' => '*/*',
                'Authorization' => static::TOKEN,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => $cookie,
                'Dnt' => '1',
                'Origin' => 'https://x.com',
                'Referer' => 'https://x.com',
                'X-Csrf-Token' => $csrfToken,
                'X-Guest-Token' => $guestToken,
                'X-Twitter-Active-User' => 'yes',
                'X-Twitter-Client-Language' => 'en',
            ];
        });
    }

    /**
     * Get the URL of profile image of the user.
     * @param  string                   $screen_name The screen_name of the user.
     * @return PromiseInterface<string> The promise.
     */
    #[\Override]
    public function getIconAsync(string $screen_name): PromiseInterface
    {
        return Coroutine::of(function () use ($screen_name) {
            try {
                $headers = yield $this->generateHeaders();
                $variables = urlencode(json_encode([
                    'screen_name' => $screen_name,
                ], JSON_THROW_ON_ERROR));
                $features = urlencode(json_encode([
                    'blue_business_profile_image_shape_enabled' => true,
                    'responsive_web_graphql_exclude_directive_enabled' => true,
                    'responsive_web_graphql_skip_user_profile_image_extensions_enabled' => true,
                    'responsive_web_graphql_timeline_navigation_enabled' => true,
                    'verified_phone_label_enabled' => true,
                ], JSON_THROW_ON_ERROR));
                $target = static::X_API_GRAPHQL_ROOT . "UserByScreenName?variables={$variables}&features={$features}";
                $response = yield $this->client->getAsync($target, [
                    'headers' => $headers,
                ]);
                $user = json_decode((string) $response->getBody());
                if (!isset($user->data->user)) {
                    throw new \RuntimeException("User not found: {$screen_name}");
                }
                $url = str_replace('_normal', '_200x200', (string) $user->data->user->result->legacy->profile_image_url_https);

                $this->repository->save(
                    $screen_name,
                    $url,
                    (new \DateTimeImmutable(static::CACHE_EXPIRE . ' seconds'))->format(\DateTimeInterface::ATOM),
                );
                return yield $url;
            } catch (\Throwable $e) {
                Log::debug($e);

                $this->profileErrorCounter->inc([
                    'service' => 'twitter',
                    'screen_name' => $screen_name,
                ]);

                return yield $this->getDefaultUrl();
            }
        });
    }

    #[\Override]
    public function getDefaultUrl(): string
    {
        return 'https://abs.twimg.com/sticky/default_profile_images/default_profile_200x200.png';
    }
}
