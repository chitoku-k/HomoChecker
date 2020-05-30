<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

/**
 * @method string loadIconTwitter(string $screen_name)
 * @method void   saveIconTwitter(string $screen_name, string $url, ...$arguments)
 * @method string loadIconMastodon(string $screen_name)
 * @method void   saveIconMastodon(string $screen_name, string $url, ...$arguments)
 */
interface CacheService
{
    /**
     * Load a value by the given key.
     * @param string $key The key.
     */
    public function load(string $key, array $arguments = []);

    /**
     * Save a value by the given key.
     * @param string $key The key.
     */
    public function save(string $key, array $arguments = []);
}
