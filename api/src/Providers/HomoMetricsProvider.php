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

final class HomoMetricsProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        $this->app->singleton(RegistryInterface::class, CollectorRegistry::class);
        $this->app->when(CollectorRegistry::class)
            ->needs(Adapter::class)
            ->give(fn (Container $app) => $app->make(APC::class));

        $this->app->singleton('collector.check_total', function (Container $app) {
            /** @var RegistryInterface $registry */
            $registry = $app->make(RegistryInterface::class);
            return $registry->registerCounter(
                'homochecker',
                'check_total',
                'The number of checks.',
                ['status', 'code', 'screen_name', 'url'],
            );
        });

        $this->app->singleton('collector.check_error_total', function (Container $app) {
            /** @var RegistryInterface $registry */
            $registry = $app->make(RegistryInterface::class);
            return $registry->registerCounter(
                'homochecker',
                'check_error_total',
                'The number of errors when checking.',
                ['status', 'code', 'screen_name', 'url'],
            );
        });

        $this->app->singleton('collector.profile_error_total', function (Container $app) {
            /** @var RegistryInterface $registry */
            $registry = $app->make(RegistryInterface::class);
            return $registry->registerCounter(
                'homochecker',
                'profile_error_total',
                'The number of errors when retrieving profiles.',
                ['service', 'screen_name'],
            );
        });

        $this->app->singleton('summary.http_server_requests_seconds', function (Container $app) {
            /** @var RegistryInterface $registry */
            $registry = $app->make(RegistryInterface::class);
            return $registry->registerSummary(
                '',
                'http_server_requests_seconds',
                'The latency for requests.',
                ['method', 'uri', 'exception', 'status', 'outcome'],
            );
        });

        $this->app->singleton(RendererInterface::class, RenderTextFormat::class);
    }

    #[\Override]
    public function provides()
    {
        return [
            RegistryInterface::class,
            RendererInterface::class,
            'collector.check_total',
            'collector.check_error_total',
            'collector.profile_error_total',
            'summary.http_server_requests_seconds',
        ];
    }
}
