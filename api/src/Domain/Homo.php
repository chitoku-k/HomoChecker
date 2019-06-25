<?php
declare(strict_types=1);

namespace HomoChecker\Domain;

class Homo
{
    /**
     * @var int The id.
     */
    protected $id;

    /**
     * @var string The screen name.
     */
    protected $screenName;

    /**
     * @var string The name of the service.
     */
    protected $service;

    /**
     * @var string The URL.
     */
    protected $url;

    /**
     * @param array|object $homo
     */
    public function __construct($homo = null)
    {
        $homo = (object) $homo;

        $this->setId($homo->id ?? null);
        $this->setScreenName($homo->screen_name ?? null);
        $this->setService($homo->service ?? null);
        $this->setUrl($homo->url ?? null);
    }

    /**
     * Get the id.
     * @return ?int The id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the id.
     * @param int $id The id.
     */
    public function setId(?int $id): void
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
}
