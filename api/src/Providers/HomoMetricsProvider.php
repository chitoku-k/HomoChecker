<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\RegistryInterface;
use Prometheus\RendererInterface;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\APC;

class HomoMetricsProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(RegistryInterface::class, CollectorRegistry::class);
        $this->app->when(CollectorRegistry::class)
            ->needs(Adapter::class)
            ->give(fn (Container $app) => $app->make(APC::class));

        $this->app->singleton('collector.check_total', function (Container $app) {
            /** @var RegistryInterface */
            $registry = $app->make(RegistryInterface::class);
            return $registry->registerCounter(
                'homochecker',
                'check_total',
                'The number of checks.',
                ['status', 'code', 'screen_name', 'url'],
            );
        });

        $this->app->singleton('collector.check_error_total', function (Container $app) {
            /** @var RegistryInterface */
            $registry = $app->make(RegistryInterface::class);
            return $registry->registerCounter(
                'homochecker',
                'check_error_total',
                'The number of errors when checking.',
                ['status', 'code', 'screen_name', 'url'],
            );
        });

        $this->app->singleton('collector.profile_error_total', function (Container $app) {
            /** @var RegistryInterface */
            $registry = $app->make(RegistryInterface::class);
            return $registry->registerCounter(
                'homochecker',
                'profile_error_total',
                'The number of errors when retrieving profiles.',
                ['service', 'screen_name'],
            );
        });

        $this->app->singleton(RendererInterface::class, RenderTextFormat::class);
    }

    public function provides()
    {
        return [
            RegistryInterface::class,
            RendererInterface::class,
        ];
    }
}
