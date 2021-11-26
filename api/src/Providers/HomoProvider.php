<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\ClientService as ClientServiceContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;
use HomoChecker\Middleware\AccessLogMiddleware;
use HomoChecker\Middleware\ErrorMiddleware;
use HomoChecker\Middleware\MetricsMiddleware;
use HomoChecker\Providers\Support\LogServiceProvider;
use HomoChecker\Service\CacheService;
use HomoChecker\Service\CheckService;
use HomoChecker\Service\ClientService;
use HomoChecker\Service\HomoService;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Prometheus\Summary;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Interfaces\RouteResolverInterface;

class HomoProvider extends ServiceProvider
{
    protected string $format = AccessLogMiddleware::FORMAT_COMBINED . ' "%{X-Forwarded-For}i"';

    public function register()
    {
        $this->app->extend(AccessLogMiddleware::class, fn (AccessLogMiddleware $log) => $log->format($this->format));
        $this->app->when(AccessLogMiddleware::class)
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

        $this->app->resolving('db', function (DatabaseManager $databaseManager, Container $app) {
            $databaseManager->extend('pgsql', function (array $config, string $name) use ($app) {
                /** @var ConnectionFactory $factory */
                $factory = $app->make('db.factory');

                // Ensure that the permission for the private key is set to 0600.
                if (isset($config['sslkey'])) {
                    $from = fopen($config['sslkey'], 'r');
                    $to = tmpfile();
                    stream_copy_to_stream($from, $to);

                    $config['sslkey'] = new class($to) {
                        public function __construct(private $handle)
                        {
                        }

                        public function __toString()
                        {
                            $metadata = stream_get_meta_data($this->handle);
                            return $metadata['uri'];
                        }
                    };
                }

                return $factory->make($config, $name);
            });
        });

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
            AccessLogMiddleware::class,
            MetricsMiddleware::class,
            ClientInterface::class,
            CacheServiceContract::class,
            CheckServiceContract::class,
            ClientServiceContract::class,
            HomoServiceContract::class,
        ];
    }
}
