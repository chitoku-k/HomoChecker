<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use HomoChecker\Handlers\ErrorHandler;
use HomoChecker\Http\Factory\ErrorResponseFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Slim\App;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Factory\StreamFactory;

class HomoHandlerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ErrorHandlerInterface::class, ErrorHandler::class);
        $this->app->when(ErrorHandler::class)
            ->needs(ResponseFactoryInterface::class)
            ->give(fn (Container $app) => $app->make(ErrorResponseFactory::class));

        $this->app->singleton(StreamFactoryInterface::class, StreamFactory::class);
        $this->app->singleton(ResponseFactoryInterface::class, function (Container $app) {
            /** @var App $slim */
            $slim = $app->make('app');
            return $slim->getResponseFactory();
        });
    }

    public function provides()
    {
        return [
            ErrorHandlerInterface::class,
            StreamFactoryInterface::class,
            ResponseFactoryInterface::class,
        ];
    }
}
