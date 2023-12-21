<?php
declare(strict_types=1);

namespace HomoChecker\Providers\Support;

use Illuminate\Log\LogManager;
use Illuminate\Log\LogServiceProvider as LogServiceProviderBase;

class LogServiceProvider extends LogServiceProviderBase
{
    #[\Override]
    public function register()
    {
        $this->app->singleton('log', function ($app) {
            return new class($app) extends LogManager {
                /**
                 * {@inheritdoc}
                 */
                #[\Override]
                protected function parseDriver($driver)
                {
                    return $driver ?? $this->getDefaultDriver();
                }
            };
        });
    }

    #[\Override]
    public function provides()
    {
        return [
            'log',
        ];
    }
}
