<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

/**
 * @method ?string loadIconMastodon(string $screen_name)
 * @method ?string loadIconTwitter(string $screen_name)
 * @method ?string loadAltsvc(string $url)
 * @method void    saveIconMastodon(string $screen_name, string $url, ...$arguments)
 * @method void    saveIconTwitter(string $screen_name, string $url, ...$arguments)
 * @method void    saveAltsvc(string $url, string $value, ...$arguments)
 */
interface CacheService
{
    /**
     * Load a value by the given key.
     * @param string $key The key.
     */
    public function load(string $key, array $arguments = []): ?string;

    /**
     * Save a value by the given key.
     * @param string $key The key.
     */
    public function save(string $key, array $arguments = []): void;
}
