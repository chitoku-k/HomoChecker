<?php
declare(strict_types=1);

namespace HomoChecker\Domain;

class Homo
{
    /**
     * @var int The id.
     */
    protected int $id;

    /**
     * @var string The screen name.
     */
    protected string $screenName;

    /**
     * @var string The name of the service.
     */
    protected string $service;

    /**
     * @var string The URL.
     */
    protected string $url;

    /**
     * @var ?Profile The profile.
     */
    protected ?Profile $profile = null;

    public function __construct(null|array|object $homo = null)
    {
        $homo = (object) $homo;

        $this->setId($homo->id);
        $this->setScreenName($homo->screen_name);
        $this->setService($homo->service);
        $this->setUrl($homo->url);

        if (isset($homo->icon_url)) {
            $this->setProfile(new Profile(['icon_url' => $homo->icon_url]));
        }
    }

    /**
     * Get the id.
     * @return int The id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the id.
     * @param int $id The id.
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the screen name.
     * @return string The screen name.
     */
    public function getScreenName(): string
    {
        return $this->screenName;
    }

    /**
     * Set the screen name.
     * @param string $screenName The screen name.
     */
    public function setScreenName(string $screenName): void
    {
        $this->screenName = $screenName;
    }

    /**
     * Get the name of the service.
     * @return string The name of the service.
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * Set the name of the service.
     * @param string $service The name of the service.
     */
    public function setService(string $service): void
    {
        $this->service = $service;
    }

    /**
     * Get the URL.
     * @return string The URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the URL.
     * @param string $url The URL.
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Get the profile.
     * @return ?Profile The profile.
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * Set the profile.
     * @param ?Profile $profile The profile.
     */
    public function setProfile(?Profile $profile): void
    {
        $this->profile = $profile;
    }
}
