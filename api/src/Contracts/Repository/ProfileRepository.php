<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Repository;

interface ProfileRepository
{
    /**
     * Save the profile.
     * @param string $screenName The screen name.
     * @param string $iconURL    The icon URL.
     * @param string $expiresAt  The expiration.
     */
    public function save(string $screenName, string $iconURL, string $expiresAt): void;
}
