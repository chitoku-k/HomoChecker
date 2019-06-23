<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

use HomoChecker\Contracts\Service\ProfileService;
use HomoChecker\Contracts\Service\ValidatorService;
use HomoChecker\Domain\Status;
use Illuminate\Support\Collection;

interface CheckService
{
    /**
     * Set the profiles.
     * @param Collection<ProfileContract> $profiles The Profiles.
     */
    public function setProfiles(Collection $profiles): void;

    /**
     * Set the validators.
     * @param Collection<ValidatorContract> $validators The Validators.
     */
    public function setValidators(Collection $validators): void;

    /**
     * Execute the checker.
     * @param  string   $screen_name The screen_name to filter by (optional).
     * @param  callable $callback    The callback that is called after resolution (optional).
     * @return Status[] The result.
     */
    public function execute(string $screen_name = null, callable $callback = null): array;
}
