<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

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
