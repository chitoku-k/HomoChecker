<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;
use HomoChecker\Service\CacheService;
use HomoChecker\Service\CheckService;
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
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\NonBufferedBody;

class HomoProvider extends ServiceProvider
{
    protected $format = AccessLog::FORMAT_COMBINED . ' "%{X-Forwarded-For}i"';

    public function register()
    {
        $this->app->bind(StreamInterface::class, NonBufferedBody::class);

        $this->app->extend(AccessLog::class, fn (AccessLog $log) => $log->format($this->format));
        $this->app->when(AccessLog::class)
            ->needs(LoggerInterface::class)
            ->give(fn () => Log::channel('router'));

        $this->app->singleton(ClientInterface::class, Client::class);
        $this->app->when(Client::class)
            ->needs('$config')
            ->give(fn (Container $app) => $app->make('config')->get('client'));

        $this->app->singleton(CacheServiceContract::class, CacheService::class);

        $this->app->singleton(CheckServiceContract::class, CheckService::class);
        $this->app->extend(CheckServiceContract::class, function (CheckServiceContract $check, Container $app) {
            $check->setProfiles($app->make('profiles'));
            $check->setValidators($app->make('validators'));
            return $check;
        });

        $this->app->singleton(HomoServiceContract::class, HomoService::class);

        (new HomoAppProvider($this->app))->register();
        (new HomoHandlerProvider($this->app))->register();
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
            StreamInterface::class,
            ClientInterface::class,
            CacheServiceContract::class,
            CheckServiceContract::class,
            HomoServiceContract::class,
        ];
    }
}
