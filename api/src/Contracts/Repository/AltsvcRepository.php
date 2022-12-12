<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Repository;

interface AltsvcRepository
{
    /**
     * Retrieve all entries.
     * @return \stdClass[] The entries.
     */
    public function findAll(): array;

    /**
     * Save the entry.
     * @param string $url      The URL.
     * @param string $protocol The protocol ID.
     * @param string $maxAge   The max age.
     */
    public function save(string $url, string $protocol, string $maxAge): void;
}
