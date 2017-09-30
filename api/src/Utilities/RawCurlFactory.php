<?php
declare(strict_types=1);

namespace HomoChecker\Utilities;

use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\EasyHandle;
use Psr\Http\Message\RequestInterface as Request;

class RawCurlFactory extends CurlFactory
{
    public function create(Request $request, array $options)
    {
        if (!defined('CURLOPT_TCP_FASTOPEN')) {
            return parent::create($request, $options);
        }
        return parent::create($request, $options + [
            'curl' => [
                CURLOPT_TCP_FASTOPEN => true,
            ],
        ]);
    }

    public function release(EasyHandle $easy)
    {
        if (isset($easy->options['on_stats_all'])) {
            static::invokeStatsAll($easy);
        }
        return parent::release($easy);
    }

    public static function invokeStatsAll(EasyHandle $easy): void
    {
        $stats = curl_getinfo($easy->handle);
        $easy->options['on_stats_all']($stats);
    }
}
