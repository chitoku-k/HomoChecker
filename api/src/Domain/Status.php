<?php
declare(strict_types=1);

namespace HomoChecker\Domain;

use TrueBV\Punycode;

class Status implements \JsonSerializable
{
    /**
     * @var ?Homo The homo object.
     */
    protected ?Homo $homo;

    /**
     * @var ?string The URL of the icon.
     */
    protected ?string $icon;

    /**
     * @var ?string The status string that represents the result of the request.
     */
    protected ?string $status;

    /**
     * @var ?string The IP address to which the server sent a request.
     */
    protected ?string $ip;

    /**
     * @var ?float The duration of the request.
     */
    protected ?float $duration;

    /**
     * @param array|object $status
     */
    public function __construct(array|object $status = null)
    {
        $status = (object) $status;

        $this->setHomo($status->homo ?? null);
        $this->setIcon($status->icon ?? null);
        $this->setStatus($status->status ?? null);
        $this->setIp($status->ip ?? null);
        $this->setDuration($status->duration ?? null);
    }

    /**
     * Get the homo object.
     * @return ?Homo The homo object.
     */
    public function getHomo(): ?Homo
    {
        return $this->homo;
    }

    /**
     * Set the homo object.
     * @param ?Homo $homo The homo object.
     */
    public function setHomo(?Homo $homo): void
    {
        $this->homo = $homo;
    }

    /**
     * Get the URL of the icon.
     * @return ?string The URL of the icon.
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Set the URL of the icon.
     * @param ?string $icon The URL of the icon.
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * Get the status string that represents the result of the request.
     * @return ?string The status string that represents the result of the request.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Set the status string that represents the result of the request.
     * @param ?string $status The status string that represents the result of the request.
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Get the IP address to which the server sent a request.
     * @return ?string The IP address to which the server sent a request.
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Set the IP address to which the server sent a request.
     * @param ?string $ip The IP address to which the server sent a request.
     */
    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * Get the duration of the request.
     * @return ?float The duration of the request.
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * Set the duration of the request.
     * @param ?float $duration The duration of the request.
     */
    public function setDuration(?float $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * Create a display URL from an absolute URL.
     * @param  string $url Absolute URL.
     * @return string Display URL.
     */
    protected function createDisplayURL(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!is_string($domain)) {
            return '';
        }
        $path = (string) parse_url($url, PHP_URL_PATH);
        return (new Punycode())->decode($domain) . $path;
    }

    /**
     * Get whether the scheme of the URL supports secure transfer.
     * @param  string $url The URL.
     * @return bool   true if supported; otherwise false.
     */
    protected function isSecure(string $url): bool
    {
        return strtolower(parse_url($url, PHP_URL_SCHEME)) === 'https';
    }

    public function getHomoArray()
    {
        return [
            'screen_name' => $this->getHomo()->getScreenName(),
            'service' => $this->getHomo()->getService(),
            'url' => $this->getHomo()->getUrl(),
            'display_url' => $this->createDisplayURL($this->getHomo()->getUrl()),
            'secure' => $this->isSecure($this->getHomo()->getUrl()),
        ];
    }

    /**
     * Return the serializable output of this object.
     * @return array The array.
     */
    public function jsonSerialize()
    {
        return [
            'homo' => $this->getHomoArray() + [
                'icon' => $this->getIcon(),
            ],
            'status' => $this->getStatus(),
            'ip' => $this->getIp(),
            'duration' => $this->getDuration(),
        ];
    }
}
