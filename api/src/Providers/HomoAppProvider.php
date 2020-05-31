<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use HomoChecker\Action\BadgeAction;
use HomoChecker\Action\CheckAction;
use HomoChecker\Action\ListAction;
use HomoChecker\Contracts\Repository\HomoRepository as HomoRepositoryContract;
use HomoChecker\Repository\HomoRepository;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Slim\Factory\AppFactory;

class HomoAppProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CheckAction::class);
        $this->app->singleton(ListAction::class);
        $this->app->singleton(BadgeAction::class);

        $this->app->singleton(HomoRepositoryContract::class, HomoRepository::class);

        $this->app->singleton('app', function (Container $app) {
            AppFactory::setContainer($app);
            $slim = AppFactory::create();
            $slim->addErrorMiddleware(true, true, true)->setDefaultErrorHandler($app->make('errorHandler'));
            $slim->get('/check[/[{name}[/]]]', CheckAction::class);
            $slim->get('/list[/[{name}[/]]]', ListAction::class);
            $slim->get('/badge[/[{status}[/]]]', BadgeAction::class);
            return $slim;
        });

        $this->app->singleton('config', fn (Container $app) => new Repository($app->make('settings')));
    }

    public function provides()
    {
        return [
            CheckAction::class,
            ListAction::class,
            BadgeAction::class,
            'app',
            'config',
        ];
    }
}
