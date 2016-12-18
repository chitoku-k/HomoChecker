<?php
namespace HomoChecker\Model;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Interop\Container\ContainerInterface as Container;

class Icon
{
    public static $default = 'https://abs.twimg.com/sticky/default_profile_images/default_profile_0_200x200.png';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getAsync(string $screen_name): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($screen_name) {
            try {
                $url = "https://twitter.com/intent/user?screen_name={$screen_name}";
                $response = yield $this->container->client->getAsync($url);
                $body = (string)$response->getBody();

                if (preg_match('/src=(?:\"|\')(https:\/\/[ap]bs\.twimg\.com\/[^\"\']+)/', $body, $matches)) {
                    list(, $url) = $matches;
                }
            } catch (RequestException $e) {
                $url = static::$default;
            }

            return yield $url;
        });
    }
}
