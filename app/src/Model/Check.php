<?php
namespace HomoChecker\Model;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Interop\Container\ContainerInterface as Container;

class Check
{
    const REDIRECT = 5;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function validateAsync(Homo $homo): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo) {
            $time = 0.0;
            $total_time = 0.0;
            $url = $homo->url;
            try {
                for ($i = 0; $i < static::REDIRECT; ++$i) {
                    $response = yield $this->container->client->getAsync($url, [
                        'on_stats_all' => function (array $stats) use (&$time, &$total_time) {
                            $time += $stats['starttransfer_time'] ?? 0;
                            $total_time += $stats['total_time'] ?? 0;
                        },
                    ]);
                    foreach ($this->container->validators as $validator) {
                        if ($status = $validator($response)) {
                            return yield [$status, $time];
                        }
                    }
                    if (!$url = $response->getHeaderLine('Location')) {
                        break;
                    }
                }
                foreach ($this->container->validators as $validator) {
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

    protected function createStatusAsync(Homo $homo, callable $callback = null): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo, $callback) {
            list(list($status, $duration), $icon) = yield Promise\all([
                $this->validateAsync($homo),
                $this->container->icon->getAsync($homo->screen_name),
            ]);
            $result = new Status($homo, $icon, $status, $duration);
            if ($callback) {
                $callback($result);
            }
            return yield $result;
        });
    }

    public function executeAsync(string $screen_name = null, callable $callback = null): Promise\PromiseInterface
    {
        $homo = new Homo($this->container);
        $users = $screen_name ? $homo->find(compact('screen_name')) : $homo->find();
        return Promise\all(
            array_map(function ($item) use ($callback) {
                return $this->createStatusAsync($item, $callback);
            }, $users)
        );
    }
}
