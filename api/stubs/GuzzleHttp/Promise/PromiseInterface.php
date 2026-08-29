<?php
declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * @psalm-yield TValue
 */
interface PromiseInterface
{
    /**
     * @psalm-yield TValue
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface;

    /**
     * @psalm-yield TValue
     */
    public function otherwise(callable $onRejected): PromiseInterface;
}
