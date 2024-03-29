<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

use GuzzleHttp\Promise\PromiseInterface;

interface ProfileService
{
    /**
     * Get the profile icon of the account by the given screen name.
     * @param  string                   $screen_name The screen name.
     * @return PromiseInterface<string> The promise that resolves with the URL string of the icon.
     */
    public function getIconAsync(string $screen_name): PromiseInterface;

    /**
     * Get the default icon URL of the service.
     * @return string The default URL.
     */
    public function getDefaultUrl(): string;
}
