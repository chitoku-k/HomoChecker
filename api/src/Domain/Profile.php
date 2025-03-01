<?php
declare(strict_types=1);

namespace HomoChecker\Domain;

final class Profile
{
    /**
     * @var string The icon URL.
     */
    private string $iconUrl;

    public function __construct(null|array|object $profile = null)
    {
        $profile = (object) $profile;

        $this->setIconUrl($profile->icon_url);
    }

    /**
     * Get the icon URL.
     * @return string The icon URL.
     */
    public function getIconUrl(): string
    {
        return $this->iconUrl;
    }

    /**
     * Set the icon URL.
     */
    public function setIconUrl(string $iconUrl): void
    {
        $this->iconUrl = $iconUrl;
    }
}
