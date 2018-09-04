<?php
declare(strict_types=1);

namespace HomoChecker\Model\Profile;

use GuzzleHttp\Promise;
use GuzzleHttp\ClientInterface;
use HomoChecker\Model\CacheInterface;

abstract class Profile implements ProfileInterface
{
    public const CACHE_EXPIRE = 300;
    public static $default = '';

    public function __construct(ClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }
}
