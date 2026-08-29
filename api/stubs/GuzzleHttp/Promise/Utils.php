<?php
declare(strict_types=1);

namespace GuzzleHttp\Promise;

final class Utils
{
    /**
     * @template    T of array
     * @param       T $promises
     * @psalm-yield T
     * @return      PromiseInterface<T>
     */
    public static function all(iterable $promises, bool $recursive = false, array $config = []): PromiseInterface {}
}
