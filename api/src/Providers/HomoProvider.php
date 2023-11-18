<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use HomoChecker\Contracts\Service\ActivityPubService as ActivityPubServiceContract;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\ClientService as ClientServiceContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;
use HomoChecker\Middleware\AccessLogMiddleware;
use HomoChecker\Middleware\ErrorMiddleware;
use HomoChecker\Middleware\MetricsMiddleware;
use HomoChecker\Providers\Support\LogServiceProvider;
use HomoChecker\Service\ActivityPubService;
use HomoChecker\Service\CheckService;
use HomoChecker\Service\ClientService;
use HomoChecker\Service\HomoService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Middlewares\AccessLog;
use Prometheus\Summary;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Interfaces\RouteResolverInterface;

class HomoProvider extends ServiceProvider
{
    protected string $format = AccessLog::FORMAT_COMBINED . ' "%{X-Forwarded-For}i"';

    public function register()
    {
        $this->app->extend(AccessLog::class, fn (AccessLog $log) => $log->format($this->format));
        $this->app->when(AccessLog::class)
            ->needs(LoggerInterface::class)
            ->give(fn () => Log::channel('router'));
        $this->app->when(AccessLogMiddleware::class)
            ->needs('$skipPaths')
            ->giveConfig('logging.skipPaths');

        $this->app->resolving(ErrorMiddleware::class, function (ErrorMiddleware $middleware, Container $app) {
            /** @var ErrorHandlerInterface $handler */
            $handler = $app->make(ErrorHandlerInterface::class);
            $middleware->setDefaultErrorHandler($handler);
        });

        $this->app->singleton(CallableResolverInterface::class, function (Container $app) {
            /** @var App $slim */
            $slim = $app->make('app');
            return $slim->getCallableResolver();
        });

        $this->app->when(MetricsMiddleware::class)
            ->needs(Summary::class)
            ->give(fn (Container $app) => $app->make('summary.http_server_requests_seconds'));
        $this->app->when(MetricsMiddleware::class)
            ->needs('$skipPaths')
            ->giveConfig('logging.skipPaths');
        $this->app->singleton(RouteResolverInterface::class, function (Container $app) {
            /** @var App $slim */
            $slim = $app->make('app');
            return $slim->getRouteResolver();
        });

        $this->app->singleton(ClientInterface::class, Client::class);
        $this->app->when(Client::class)
            ->needs('$config')
            ->giveConfig('client');

        $this->app->singleton(ActivityPubServiceContract::class, ActivityPubService::class);
        $this->app->when(ActivityPubService::class)
            ->needs('$id')
            ->give(fn (Container $app) => $app->make('config')->get('activityPub.actor')['id']);
        $this->app->when(ActivityPubService::class)
            ->needs('$publicKeyPem')
            ->give(function (Container $app) {
                $actor = $app->make('config')->get('activityPub.actor');
                return \file_get_contents($actor['public_key']);
            });

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
    }

    public function provides()
    {
        return [
            AccessLogMiddleware::class,
            CallableResolverInterface::class,
            MetricsMiddleware::class,
            ClientInterface::class,
            CheckServiceContract::class,
            ClientServiceContract::class,
            HomoServiceContract::class,
        ];
    }
}
