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

    public function __construct()
    {
        $this->validators = [
            new HeaderValidator,
            new DOMValidator,
            new URLValidator,
        ];
    }

    public function initialize(string $url, bool $body)
    {
        $ssl = parse_url($url, PHP_URL_SCHEME) === 'https';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLINFO_HEADER_OUT       => true,
            CURLOPT_AUTOREFERER       => true,
            CURLOPT_CONNECTTIMEOUT_MS => self::TIMEOUT,
            CURLOPT_FOLLOWLOCATION    => true,
            CURLOPT_MAXREDIRS         => 5,
            CURLOPT_NOBODY            => !$body,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_SSL_VERIFYPEER    => false, // TODO: Insecure ass
            CURLOPT_TCP_FASTOPEN      => !$ssl,
            CURLOPT_TIMEOUT_MS        => self::TIMEOUT,
            CURLOPT_USERAGENT         => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ]);
        return $ch;
    }

    protected function validate(Homo $homo): \Generator
    {
        list($header_validator) = $this->validators;

        yield $ch = $this->initialize($homo->url, false);
        $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

        if (($status = $header_validator($ch, ''))) {
            return [$status, $time];
        }

        $body = yield $ch = $this->initialize($homo->url, true);
        $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

        if ($body instanceof CURLException || !curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            return ['ERROR', $time];
        }

        foreach ($this->validators as $validator) {
            if (($status = $validator($ch, $body))) {
                return [$status, $time];
            }
        }

        return ['WRONG', $time];
    }

    public function execute(string $screen_name = null, callable $callback = null): array
    {
        $homos = isset($screen_name) ? Homo::getByScreenName($screen_name) : Homo::getAll();

        return Co::wait(array_map(function ($homo) use ($callback): \Generator {
            list(list($status, $duration), $icon) = yield [
                $this->validate($homo),
                Icon::get($homo->screen_name),
            ];
            $response = new HomoResponse($homo, $icon, $status, $duration);
            if ($callback) {
                $callback($response);
            }
            return $response;
        }, $homos), [
            'concurrency' => 32,
            'interval'    => 0,
            'pipeline'    => true,
            'throw'       => false,
        ]);
    }
}
