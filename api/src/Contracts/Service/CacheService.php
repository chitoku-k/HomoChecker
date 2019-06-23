<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

interface CacheService
{
    public function load(string $key, array $arguments = []);
    public function save(string $key, array $arguments = []);
}
