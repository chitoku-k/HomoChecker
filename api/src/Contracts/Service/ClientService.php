<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

use Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use HomoChecker\Contracts\Service\Client\Response;

interface ClientService extends ClientInterface
{
    /**
     * Get the responses for URL.
     * @param  string                                $url The URL.
     * @return Generator<PromiseInterface<Response>> The responses.
     */
    public function getAsync(string $url): Generator;
}
