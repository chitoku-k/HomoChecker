<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use HomoChecker\Contracts\Service\Client\Response;

interface ClientService extends ClientInterface
{
    /**
     * Get the responses for URL.
     * @param  string                                         $url The URL.
     * @return \Generator<string, PromiseInterface<Response>> The responses.
     */
    public function getRedirectsAsync(string $url): \Generator;
}
