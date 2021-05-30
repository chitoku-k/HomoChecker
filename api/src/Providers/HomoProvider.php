<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\ClientService as ClientServiceContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;
use HomoChecker\Service\CacheService;
use HomoChecker\Service\CheckService;
use HomoChecker\Service\ClientService;
use HomoChecker\Service\HomoService;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Middlewares\AccessLog;
use Psr\Log\LoggerInterface;

class HomoProvider extends ServiceProvider
{
    protected $format = AccessLog::FORMAT_COMBINED . ' "%{X-Forwarded-For}i"';

    public function register()
    {
        $this->app->extend(AccessLog::class, fn (AccessLog $log) => $log->format($this->format));
        $this->app->when(AccessLog::class)
            ->needs(LoggerInterface::class)
            ->give(fn () => Log::channel('router'));

        $this->app->singleton(ClientInterface::class, Client::class);
        $this->app->when(Client::class)
            ->needs('$config')
            ->giveConfig('client');

        $this->app->singleton(CacheServiceContract::class, CacheService::class);

        $this->app->singleton(CheckServiceContract::class, fn (Container $app) => new CheckService(
            $app->make(ClientServiceContract::class),
            $app->make(HomoServiceContract::class),
            $app->make('collector.check_total'),
            $app->make('collector.check_error_total'),
            $app->make('profiles'),
            $app->make('validators'),
        ));

        $this->app->singleton(ClientServiceContract::class, ClientService::class);
        $this->app->when(ClientService::class)
            ->needs('$redirect')
            ->giveConfig('client.redirect');

        $this->app->singleton(HomoServiceContract::class, HomoService::class);

        (new HomoAppProvider($this->app))->register();
        (new HomoHandlerProvider($this->app))->register();
        (new HomoMetricsProvider($this->app))->register();
        (new HomoProfileServiceProvider($this->app))->register();
        (new HomoValidatorServiceProvider($this->app))->register();
        (new EventServiceProvider($this->app))->register();
        (new LogServiceProvider($this->app))->register();
        (new DatabaseServiceProvider($this->app))->register();
        (new CacheServiceProvider($this->app))->register();
        (new RedisServiceProvider($this->app))->register();
    }

    public function provides()
    {
        return [
            AccessLog::class,
            ClientInterface::class,
            CacheServiceContract::class,
            CheckServiceContract::class,
            ClientServiceContract::class,
            HomoServiceContract::class,
        ];
    }
}
