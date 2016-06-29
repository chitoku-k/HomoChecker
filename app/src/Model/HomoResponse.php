<?php
namespace HomoChecker\Model;

use TrueBV\Punycode;

class HomoResponse {
    public function __construct(Homo $homo, Icon $icon, string $status, float $duration) {
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

    protected function createDisplayURL(string $url): string {
        if (!preg_match('/(https?:\/\/)(.*)(:\d+)?(\/.*)?/', $url, $matches)) {
            return '';
        }
        list(, $protocol, $domain, $port, $path) = $matches;
        return (new Punycode)->decode($domain) . $path;
    }

    protected function isSecure(string $url): bool {
        return strpos($url, 'https://') === 0;
    }
}
