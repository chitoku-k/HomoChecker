<?php
declare(strict_types=1);

namespace HomoChecker\Model;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\RequestException;
use HomoChecker\Model\Profile\Icon;
use HomoChecker\Model\Validator\ValidatorInterface;

class Check
{
    public const REDIRECT = 5;

    public function __construct(ClientInterface $client, Homo $homo, Icon $icon, ValidatorInterface ...$validators)
    {
        $this->client = $client;
        $this->homo = $homo;
        $this->icon = $icon;
        $this->validators = $validators;
    }

    /**
     * Validate a user.
     * @param  Homo                     $homo The user.
     * @return Promise\PromiseInterface The Promise.
     */
    protected function validateAsync(Homo $homo): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo) {
            $time = 0.0;
            $total_time = 0.0;
            $ip = '';
            $url = $homo->url;
            try {
                for ($i = 0; $i < static::REDIRECT; ++$i) {
                    $response = yield $this->client->getAsync($url, [
                        RequestOptions::ON_STATS => function (TransferStats $stats) use (&$time, &$total_time, &$ip) {
                            $time += $stats->getHandlerStat('starttransfer_time') ?? 0;
                            $total_time += $stats->getHandlerStat('total_time') ?? 0;
                            $ip = $stats->getHandlerStat('primary_ip') ?? '';
                        },
                    ]);
                    foreach ($this->validators as $validator) {
                        if ($status = $validator($response)) {
                            return yield [$status, $ip, $time];
                        }
                    }
                    if (!$url = $response->getHeaderLine('Location')) {
                        break;
                    }
                }
                return yield ['WRONG', $ip, $time];
            } catch (RequestException $e) {
                return yield ['ERROR', $ip, $total_time];
            }
        });
    }

    /**
     * Create a status object from a user.
     * @param  Homo                     $homo     The user.
     * @param  callable                 $callback The callback that is called after resolution (optional).
     * @return Promise\PromiseInterface The Promise.
     */
    protected function createStatusAsync(Homo $homo, callable $callback = null): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo, $callback) {
            [[$status, $ip, $duration], $icon] = yield Promise\all([
                $this->validateAsync($homo),
                $this->icon->getAsync($homo->screen_name),
            ]);
            $result = new Status($homo, $icon, $status, $ip, $duration);
            if ($callback) {
                $callback($result);
            }
            return yield $result;
        });
    }

    /**
     * Execute the checker.
     * @param  string   $screen_name The screen_name to filter by (optional).
     * @param  callable $callback    The callback that is called after resolution (optional).
     * @return Status[] The result.
     */
    public function execute(string $screen_name = null, callable $callback = null): array
    {
        $users = $this->homo->find($screen_name ? compact('screen_name') : []);
        return Pool::batch(
            $this->client,
            array_map(function ($item) use ($callback): callable {
                return function () use ($item, $callback): Promise\PromiseInterface {
                    return $this->createStatusAsync($item, $callback);
                };
            }, $users),
            [
                'concurrency' => 4,
            ]
        );
    }
}
