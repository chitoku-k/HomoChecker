<?php
namespace HomoChecker\Model;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Interop\Container\ContainerInterface as Container;

class Icon
{
    public static $default = 'https://abs.twimg.com/sticky/default_profile_images/default_profile_0_200x200.png';

    public $screen_name;
    public $url;

    public function __construct(Container $container, string $screen_name)
    {
        $this->container = $container;
        $this->screen_name = $screen_name;
    }

    protected function fetchAsync(): Promise\PromiseInterface
    {
        return Promise\coroutine(function () {
            try {
                $url = "https://twitter.com/intent/user?screen_name={$this->screen_name}";
                $response = yield $this->container->client->getAsync($url);
                $body = (string)$response->getBody();

                if (preg_match('/src=(?:\"|\')(https:\/\/[ap]bs\.twimg\.com\/[^\"\']+)/', $body, $matches)) {
                    list(, $this->url) = $matches;
                }
            } catch (RequestException $e) {
                $this->url = static::$default;
            }

            return yield $this;
        });
    }

    public static function getAsync(Container $container, string $screen_name): Promise\PromiseInterface
    {
        $self = new static($container, $screen_name);
        return $self->fetchAsync();
    }
}
