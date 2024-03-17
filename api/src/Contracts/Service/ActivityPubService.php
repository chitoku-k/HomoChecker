<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

interface ActivityPubService
{
    /**
     * Describes this application.
     * @return array The actor object that describes this application.
     */
    public function actor(): array;

    /**
     * Retrieves the Web Finger of the given resource.
     */
    public function webFinger(string $resource): ?array;
}
