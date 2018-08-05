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
use HomoChecker\Model\Validator\ValidatorResult;

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

    private function getValidateStatus(Homo $homo, string $status, array $ips, $times): array
    {
        if (!is_array($times)) {
            return [$status, $ips[$homo->url], $times];
        }

        $offset = array_search($homo->url, array_keys($ips)) + 1;
        array_splice($ips, $offset);
        array_splice($times, $offset);

        return [$status, array_pop($ips), array_sum($times)];
    }

    /**
     * Validate a user.
     * @param  Homo                     $homo The user.
     * @return Promise\PromiseInterface The Promise.
     */
    protected function validateAsync(Homo $homo): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo) {
            $total_time = 0.0;
            $times = [];
            $ips = [];
            $url = $homo->url;
            try {
                for ($i = 0; $i < static::REDIRECT; ++$i) {
                    $response = yield $this->client->getAsync($url, [
                        RequestOptions::ON_STATS => function (TransferStats $stats) use ($url, &$times, &$total_time, &$ips) {
                            $total_time += $stats->getHandlerStat('total_time') ?? 0;
                            $times[$url] = $stats->getHandlerStat('starttransfer_time') ?? 0;
                            $ips[$url] = $stats->getHandlerStat('primary_ip') ?? null;
                        },
                    ]);
                    foreach ($this->validators as $validator) {
                        if (!$status = $validator($response)) {
                            continue;
                        }
                        return yield $this->getValidateStatus($homo, $status, $ips, $times);
                    }
                    if (!$url = $response->getHeaderLine('Location')) {
                        break;
                    }
                }

                return yield $this->getValidateStatus($homo, ValidatorResult::WRONG, $ips, $times);
            } catch (RequestException $e) {
                return yield $this->getValidateStatus($homo, ValidatorResult::ERROR, $ips, $total_time);
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
