<?php
namespace HomoChecker\Utilities;

use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\CurlFactoryInterface;
use GuzzleHttp\Handler\EasyHandle;

class RawCurlFactory extends CurlFactory
{
    public function release(EasyHandle $easy)
    {
        if (isset($easy->options['on_stats_all'])) {
            static::invokeStatsAll($easy);
        }
        return parent::release($easy);
    }

    public static function invokeStatsAll(EasyHandle $easy)
    {
        $stats = curl_getinfo($easy->handle);
        call_user_func($easy->options['on_stats_all'], $stats);
    }
}
