<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

use HomoChecker\Domain\Status;

interface CheckService
{
    /**
     * Execute the checker.
     * @param  string   $screen_name The screen_name to filter by (optional).
     * @param  callable $callback    The callback that is called after resolution (optional).
     * @return Status[] The result.
     */
    public function execute(string $screen_name = null, callable $callback = null): array;
}
