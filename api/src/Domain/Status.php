<?php
declare(strict_types=1);

namespace HomoChecker\Domain;

use TrueBV\Punycode;

class Status implements \JsonSerializable
{
    /**
     * @var Homo The homo object.
     */
    protected Homo $homo;

    /**
     * @var Result The result object.
     */
    protected Result $result;

    /**
     * @var ?string The icon URL.
     */
    protected ?string $icon;

    /**
     * @param array|object $status
     */
    public function __construct(array|object $status = null)
    {
        $status = (object) $status;

        $this->setHomo($status->homo ?? new Homo());
        $this->setResult($status->result ?? new Result());
        $this->setIcon($status->icon ?? null);
    }

    /**
     * Get the homo object.
     * @return Homo The homo object.
     */
    public function getHomo(): Homo
    {
        return $this->homo;
    }

    /**
     * Set the homo object.
     * @param Homo $homo The homo object.
     */
    public function setHomo(Homo $homo): void
    {
        $this->homo = $homo;
    }

    /**
     * Get the result object.
     * @return Result The result object.
     */
    public function getResult(): Result
    {
        return $this->result;
    }

    /**
     * Set the result object.
     * @param Result $result The result object.
     */
    public function setResult(Result $result): void
    {
        $this->result = $result;
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
     * Create a display URL from an absolute URL.
     * @param  ?string $url    Absolute URL.
     * @param  bool    $scheme Scheme.
     * @return string  Display URL.
     */
    protected function createDisplayURL(?string $url, bool $scheme = false): string
    {
        if (!$url) {
            return '';
        }

        $domain = parse_url($url, PHP_URL_HOST);
        if (!is_string($domain)) {
            return '';
        }

        $scheme = $scheme ? parse_url($url, PHP_URL_SCHEME) . '://' : '';
        $path = (string) parse_url($url, PHP_URL_PATH);
        return $scheme . (new Punycode())->decode($domain) . $path;
    }

    /**
     * Get whether the scheme of the URL supports secure transfer.
     * @param  ?string $url The URL.
     * @return bool    true if supported; otherwise false.
     */
    protected function isSecure(?string $url): bool
    {
        return match ($url) {
            null => false,
            default => strtolower(parse_url($url, PHP_URL_SCHEME)) === 'https',
        };
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

    public function getResultArray()
    {
        return [
            'status' => $this->getResult()->getStatus(),
            'code' => $this->getResult()->getCode(),
            'ip' => $this->getResult()->getIp(),
            'url' => $this->createDisplayURL($this->getResult()->getUrl(), true),
            'duration' => $this->getResult()->getDuration(),
            'error' => $this->getResult()->getError(),
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
        ] + $this->getResultArray();
    }
}
