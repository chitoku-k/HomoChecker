<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Repository;

interface HomoRepository
{
    /**
     * Get the number of the entries.
     * @return int The number of the entries.
     */
    public function count(): int;

    /**
     * Get the number of the entries whose screen name matches the given one.
     * @param  string $screenName The screen name.
     * @return int    The number of the entries.
     */
    public function countByScreenName(string $screenName): int;

    /**
     * Retrieve all entries.
     * @return \stdClass[] The entries.
     */
    public function findAll(): array;

    /**
     * Retrieve the entries that matches the given screen name.
     * @param  string      $screenName The screen name.
     * @return \stdClass[] The entries.
     */
    public function findByScreenName(string $screenName): array;

    /**
     * Export all entries into a query expression.
     * @return string The query expression.
     */
    public function export(): string;
}
