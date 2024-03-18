<?php
declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * @template-covariant T
 * @psalm-yield        T
 */
interface PromiseInterface
{
    /**
     * @return PromiseInterface<T>
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null): PromiseInterface;

    /**
     * @return PromiseInterface<T>
     */
    public function otherwise(callable $onRejected): PromiseInterface;
}
