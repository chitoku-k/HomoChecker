<?php
namespace HomoChecker\Model;

use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use HomoChecker\Model\Validator\HeaderValidator;
use HomoChecker\Model\Validator\DOMValidator;
use HomoChecker\Model\Validator\URLValidator;
use Interop\Container\ContainerInterface as Container;

class Check
{
    const REDIRECT = 5;

    public function __construct(Container $container, callable $callback = null)
    {
        $this->container = $container;
        $this->callback = $callback;
    }

    protected function validateAsync(Homo $homo): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo) {
            $time = 0.0;
            $url = $homo->url;
            try {
                for ($i = 0; $i < self::REDIRECT; ++$i) {
                    $response = yield $this->container->client->getAsync($url, [
                        'on_stats' => function (TransferStats $stats) use (&$time) {
                            $time += $stats->getTransferTime();
                        },
                    ]);
                    if (($status = (new HeaderValidator)($response))) {
                        return yield [$status, $time];
                    }
                    if (!isset($response->getHeaders()['Location'])) {
                        break;
                    }
                }
                foreach ([new DOMValidator, new URLValidator] as $validator) {
                    if (($status = $validator($response))) {
                        return yield [$status, $time];
                    }
                }
                return yield ['WRONG', $time];
            } catch (RequestException $e) {
                return yield ['ERROR', $time];
            }
        });
    }

    protected function createStatusAsync(Homo $homo): Promise\PromiseInterface {
        return Promise\coroutine(function () use ($homo) {
            list(list($status, $duration), $icon) = yield Promise\all([
                $this->validateAsync($homo),
                Icon::getAsync($this->container, $homo->screen_name),
            ]);
            $result = new Status($homo, $icon, $status, $duration);
            if ($this->callback) {
                ($this->callback)($result);
            }
            return yield $result;
        });
    }

    public function executeAsync(string $screen_name = null): Promise\PromiseInterface
    {
        $homos = isset($screen_name) ? Homo::getByScreenName($screen_name) : Homo::getAll();
        return Promise\all(
            array_map([$this, 'createStatusAsync'], iterator_to_array($homos))
        );
    }
}
