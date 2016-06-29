<?php
namespace HomoChecker\Model;

class HomoResponse {
    public function __construct(Homo $homo, Icon $icon, string $status, float $duration) {
        $this->homo = [
            'screen_name' => $homo->screen_name,
            'url'         => $homo->url,
            'icon'        => $icon->url,
        ];
        $this->status = $status;
        $this->duration = round($duration, 2);
    }
}
