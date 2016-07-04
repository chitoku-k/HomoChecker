<?php
namespace HomoChecker\Model;

use TrueBV\Punycode;

class HomoResponse
{
    public function __construct(Homo $homo, Icon $icon, string $status, float $duration)
    {
        $this->homo = [
            'screen_name' => $homo->screen_name,
            'url'         => $homo->url,
            'display_url' => $this->createDisplayURL($homo->url),
            'secure'      => $this->isSecure($homo->url),
            'icon'        => $icon->url,
        ];
        $this->status = $status;
        $this->duration = round($duration, 2);
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
        return strpos($url, 'https://') === 0;
    }
}
