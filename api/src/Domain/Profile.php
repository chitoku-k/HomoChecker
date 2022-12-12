<?php
declare(strict_types=1);

namespace HomoChecker\Domain;

class Profile
{
    /**
     * @var ?string The icon URL.
     */
    protected ?string $iconUrl;

    /**
     * @param array|object $profile
     */
    public function __construct(array|object $profile = null)
    {
        $profile = (object) $profile;

        $this->setIconUrl($profile->icon_url ?? null);
    }

    /**
     * Get the icon URL.
     * @return ?string The icon URL.
     */
    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    /**
     * Set the icon URL.
     */
    public function setIconUrl(?string $iconUrl): void
    {
        $this->iconUrl = $iconUrl;
    }
}
