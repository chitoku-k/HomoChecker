<?php
namespace HomoChecker\Model;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise;
use GuzzleHttp\ClientInterface;
use HomoChecker\Model\HomoInterface;
use HomoChecker\Model\Validator\ValidatorInterface;

class Check
{
    public const REDIRECT = 5;

    public function __construct(ClientInterface $client, HomoInterface $homo, IconInterface $icon, ValidatorInterface ...$validators)
    {
        $this->client = $client;
        $this->homo = $homo;
        $this->icon = $icon;
        $this->validators = $validators;
    }

    protected function validateAsync(HomoInterface $homo): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo) {
            $time = 0.0;
            $total_time = 0.0;
            $url = $homo->url;
            try {
                for ($i = 0; $i < static::REDIRECT; ++$i) {
                    $response = yield $this->client->getAsync($url, [
                        'on_stats_all' => function (array $stats) use (&$time, &$total_time) {
                            $time += $stats['starttransfer_time'] ?? 0;
                            $total_time += $stats['total_time'] ?? 0;
                        },
                    ]);
                    foreach ($this->validators as $validator) {
                        if ($status = $validator($response)) {
                            return yield [$status, $time];
                        }
                    }
                    if (!$url = $response->getHeaderLine('Location')) {
                        break;
                    }
                }
                foreach ($this->validators as $validator) {
                    if ($status = $validator($response)) {
                        return yield [$status, $time];
                    }
                }
                return yield ['WRONG', $time];
            } catch (RequestException $e) {
                return yield ['ERROR', $total_time];
            }
        });
    }

    protected function createStatusAsync(HomoInterface $homo, callable $callback = null): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo, $callback) {
            [[$status, $duration], $icon] = yield Promise\all([
                $this->validateAsync($homo),
                $this->icon->getAsync($homo->screen_name),
            ]);
            $result = new Status($homo, $icon, $status, $duration);
            if ($callback) {
                $callback($result);
            }
            return yield $result;
        });
    }

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
                'concurrency' => 32,
            ]
        );
    }
}
