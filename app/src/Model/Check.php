<?php
namespace HomoChecker\Model;

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use HomoChecker\Model\Validator\HeaderValidator;
use HomoChecker\Model\Validator\DOMValidator;
use HomoChecker\Model\Validator\URLValidator;

class Check
{
    const TIMEOUT = 5000;
    const REDIRECT = 5;

    public function __construct(callable $callback = null)
    {
        $this->callback = $callback;
    }

    public function initialize(string $url)
    {
        $ssl = parse_url($url, PHP_URL_SCHEME) === 'https';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLINFO_HEADER_OUT       => true,
            CURLOPT_AUTOREFERER       => true,
            CURLOPT_CONNECTTIMEOUT_MS => self::TIMEOUT,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_TCP_FASTOPEN      => !$ssl,
            CURLOPT_TIMEOUT_MS        => self::TIMEOUT,
            CURLOPT_USERAGENT         => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ]);
        return $ch;
    }

    protected function validate(Homo $homo): \Generator
    {
        $time = 0.0;
        $url = $homo->url;
        try {
            for ($i = 0; $i < self::REDIRECT; ++$i) {
                yield $ch = $this->initialize($url);
                $time += curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME);
                if (($status = (new HeaderValidator)($ch))) {
                    return [$status, $time];
                }
                if (false === ($url = curl_getinfo($ch, CURLINFO_REDIRECT_URL))) {
                    break;
                }
            }
            foreach ([new DOMValidator, new URLValidator] as $validator) {
                if (($status = $validator($ch))) {
                    return [$status, $time];
                }
            }
            return ['WRONG', $time];
        } catch (CURLException $e) {
            return ['ERROR', curl_getinfo($ch, CURLINFO_TOTAL_TIME)];
        }
    }

    protected function createResponse(Homo $homo): \Generator {
        list(list($status, $duration), $icon) = yield [
            $this->validate($homo),
            Icon::get($homo->screen_name),
        ];
        $response = new HomoResponse($homo, $icon, $status, $duration);
        if ($this->callback) {
            ($this->callback)($response);
        }
        return $response;
    }

    public function execute(string $screen_name = null, callable $callback = null): array
    {
        $homos = isset($screen_name) ? Homo::getByScreenName($screen_name) : Homo::getAll();
        return Co::wait(array_map([$this, 'createResponse'], iterator_to_array($homos)), [
            'concurrency'  => 32,
            'autoschedule' => true,
        ]);
    }
}
