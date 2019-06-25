<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;
use HomoChecker\Contracts\View\ServerSentEventView as ServerSentEventViewContract;
use HomoChecker\Service\CacheService;
use HomoChecker\Service\CheckService;
use HomoChecker\Service\HomoService;
use HomoChecker\View\ServerSentEventView;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\ServiceProvider;
use Slim\DefaultServicesProvider;

class HomoProvider extends ServiceProvider
{
    public function boot()
    {
        Model::setConnectionResolver($this->app->get('db'));
    }

    public function register()
    {
        $this->app->singleton(ClientInterface::class, function (Container $app) {
            return new Client($app->make('config')->get('client'));
        });

        $this->app->singleton(CacheServiceContract::class, CacheService::class);

        $this->app->singleton(CheckServiceContract::class, CheckService::class);
        $this->app->extend(CheckServiceContract::class, function (CheckServiceContract $check, Container $app) {
            $check->setProfiles($app->make('profiles'));
            $check->setValidators($app->make('validators'));
            return $check;
        });

        $this->app->singleton(HomoServiceContract::class, HomoService::class);

        $this->app->singleton(ServerSentEventViewContract::class, function (Container $app) {
            return new ServerSentEventView('response');
        });

        (new DefaultServicesProvider())->register($this->app);
        (new HomoAppProvider($this->app))->register();
        (new HomoHandlerProvider($this->app))->register();
        (new HomoProfileServiceProvider($this->app))->register();
        (new HomoValidatorServiceProvider($this->app))->register();
        (new DatabaseServiceProvider($this->app))->register();
        (new CacheServiceProvider($this->app))->register();
        (new RedisServiceProvider($this->app))->register();
    }

    public function provides()
    {
        return [
            ClientInterface::class,
            CacheServiceContract::class,
            CheckServiceContract::class,
            HomoServiceContract::class,
            HomoRepositoryContract::class,
            ServerSentEventViewContract::class,
        ];
    }
}
