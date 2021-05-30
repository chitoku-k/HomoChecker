<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use HomoChecker\Action\BadgeAction;
use HomoChecker\Action\CheckAction;
use HomoChecker\Action\HealthCheckAction;
use HomoChecker\Action\ListAction;
use HomoChecker\Action\MetricsAction;
use HomoChecker\Contracts\Repository\HomoRepository as HomoRepositoryContract;
use HomoChecker\Http\NonBufferedBody;
use HomoChecker\Repository\HomoRepository;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Middlewares\AccessLog;
use Psr\Http\Message\StreamInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\ErrorHandlerInterface;

class HomoAppProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(HealthCheckAction::class);
        $this->app->singleton(MetricsAction::class);
        $this->app->singleton(CheckAction::class);
        $this->app->when(CheckAction::class)
            ->needs(StreamInterface::class)
            ->give(fn (Container $app) => $app->make(NonBufferedBody::class));

        $this->app->singleton(ListAction::class);
        $this->app->singleton(BadgeAction::class);

        $this->app->singleton(HomoRepositoryContract::class, HomoRepository::class);

        $this->app->singleton('app', function (Container $app) {
            AppFactory::setContainer($app);
            $slim = AppFactory::create();
            $slim->addMiddleware($app->make(AccessLog::class));
            $slim->get('/healthz', HealthCheckAction::class);
            $slim->get('/metrics', MetricsAction::class);
            $slim->get('/check[/[{name}[/]]]', CheckAction::class);
            $slim->get('/list[/[{name}[/]]]', ListAction::class);
            $slim->get('/badge[/[{status}[/]]]', BadgeAction::class);
            return $slim;
        });
        $this->app->resolving('app', function (App $slim, Container $app) {
            $slim->addErrorMiddleware(true, true, true)->setDefaultErrorHandler($app->make(ErrorHandlerInterface::class));
        });

        $this->app->singleton('config', Repository::class);
        $this->app->when(Repository::class)
            ->needs('$items')
            ->give(fn (Container $app) => $app->make('settings'));
    }

    public function provides()
    {
        return [
            HealthCheckAction::class,
            CheckAction::class,
            ListAction::class,
            BadgeAction::class,
            HomoRepositoryContract::class,
            'app',
            'config',
        ];
    }
}
