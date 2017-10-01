<?php
declare(strict_types=1);

namespace HomoChecker\Model;

use TrueBV\Punycode;

class Status
{
    public function __construct(HomoInterface $homo, string $icon = null, string $status = null, float $duration = null)
    {
        $this->homo = (object)[
            'screen_name' => $homo->screen_name,
            'url' => $homo->url,
            'display_url' => $this->createDisplayURL($homo->url),
            'secure' => $this->isSecure($homo->url),
        ];

        if (isset($icon)) {
            $this->homo->icon = $icon;
        }
        if (isset($status)) {
            $this->status = $status;
        }
        if (isset($duration)) {
            $this->duration = $duration;
        }
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
        $path = (string)parse_url($url, PHP_URL_PATH);
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
}
