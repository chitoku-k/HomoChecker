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
use HomoChecker\Middleware\AccessLogMiddleware;
use HomoChecker\Middleware\ErrorMiddleware;
use HomoChecker\Middleware\MetricsMiddleware;
use HomoChecker\Repository\HomoRepository;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\StreamInterface;
use Slim\App;
use Slim\Factory\AppFactory;

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
            $slim->get('/healthz', HealthCheckAction::class);
            $slim->get('/metrics', MetricsAction::class);
            $slim->get('/check[/[{name}[/]]]', CheckAction::class);
            $slim->get('/list[/[{name}[/]]]', ListAction::class);
            $slim->get('/badge[/[{status}[/]]]', BadgeAction::class);
            return $slim;
        });
        $this->app->resolving('app', function (App $slim, Container $app) {
            $slim->addRoutingMiddleware();

            // For errors occurred in routing and actions
            $slim->addMiddleware($app->make(ErrorMiddleware::class));
            $slim->addMiddleware($app->make(AccessLogMiddleware::class));
            $slim->addMiddleware($app->make(MetricsMiddleware::class));

            // For errors occurred in middleware
            $slim->addMiddleware($app->make(ErrorMiddleware::class));
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
            MetricsAction::class,
            CheckAction::class,
            ListAction::class,
            BadgeAction::class,
            HomoRepositoryContract::class,
            'app',
            'config',
        ];
    }
}
