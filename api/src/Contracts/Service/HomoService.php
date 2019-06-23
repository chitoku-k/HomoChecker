<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

use HomoChecker\Domain\Homo;

interface HomoService
{
    /**
     * Get the number of the entries.
     * @return int The number of the entries.
     */
    public function count(string $screenName = null): int;

    /**
     * Retrieve all entries or matched entries if the screen name is given.
     * @return Homo[] The entries.
     */
    public function find(string $screenName = null): array;

    /**
     * Export all entries into a query expression.
     * @return string The query expression.
     */
    public function export(): string;
}
