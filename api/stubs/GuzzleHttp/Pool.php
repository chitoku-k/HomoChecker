<?php
declare(strict_types=1);

namespace GuzzleHttp;

class Pool
{
    /**
     * @param iterable<array-key, mixed> $requests
     */
    public static function batch(ClientInterface $client, iterable $requests, #[\SensitiveParameter] array $options = []): array {}
}
