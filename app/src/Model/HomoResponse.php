<?php
namespace HomoChecker\Model;

use TrueBV\Punycode;

class HomoResponse
{
    public function __construct(Homo $homo, Icon $icon = null, string $status = null, float $duration = null)
    {
        $this->homo = [
            'screen_name' => $homo->screen_name,
            'url'         => $homo->url,
            'display_url' => $this->createDisplayURL($homo->url),
            'secure'      => $this->isSecure($homo->url),
        ];

        if (isset($icon)) {
            $this->homo['icon'] = $icon->url;
        }
        if (isset($status)) {
            $this->status = $status;
        }
        if (isset($duration)) {
            $this->duration = $duration;
        }
    }

    protected function createDisplayURL(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!is_string($domain)) {
            return '';
        }
        $path = (string)parse_url($url, PHP_URL_PATH);
        return (new Punycode)->decode($domain) . $path;
    }

    protected function isSecure(string $url): bool
    {
        return parse_url($url, PHP_URL_SCHEME) === 'https';
    }
}
