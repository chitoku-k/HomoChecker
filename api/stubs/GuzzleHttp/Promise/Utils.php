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
    public static function all($promises, bool $recursive = false): PromiseInterface {}
}
