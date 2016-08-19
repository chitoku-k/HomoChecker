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

    public function __construct(callable $callback = null)
    {
        $this->callback = $callback;
        $this->validators = [
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
            CURLOPT_TCP_FASTOPEN      => !$ssl,
            CURLOPT_TIMEOUT_MS        => self::TIMEOUT,
            CURLOPT_USERAGENT         => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
        ]);
        return $ch;
    }

    protected function validate(Homo $homo): \Generator
    {
        try {
            yield $ch = $this->initialize($homo->url, false);
            $time = curl_getinfo($ch, CURLINFO_REDIRECT_TIME) - curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME);

            if (($status = (new HeaderValidator)($ch, ''))) {
                return [$status, $time];
            }

            $body = yield $ch = $this->initialize($homo->url, true);
            $time = curl_getinfo($ch, CURLINFO_REDIRECT_TIME) + curl_getinfo($ch, CURLINFO_TOTAL_TIME) - curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME);
            if (!curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                throw new \RuntimeException('Invalid HTTP code', 500);
            }
        } catch (\RuntimeException $e) {
            $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            return ['ERROR', $time];
        }

        foreach ($this->validators as $validator) {
            if (($status = $validator($ch, $body))) {
                return [$status, $time];
            }
        }

        return ['WRONG', $time];
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
            'concurrency' => 32,
            'interval'    => 0,
            'pipeline'    => true,
        ]);
    }
}
