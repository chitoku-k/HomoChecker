<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

interface HomoService
{
    /**
     * Get the number of the entries.
     * @param  string $screenName The screen name.
     * @return int                The number of the entries.
     */
    public function count(string $screenName = null): int;

    /**
     * Retrieve all entries or matched entries if the screen name is given.
     * @param  string      $screenName The screen name.
     * @return \stdClass[]             The entries.
     */
    public function find(string $screenName = null): array;

    /**
     * Export all entries into a query expression.
     * @return string The query expression.
     */
    public function export(): string;
}
