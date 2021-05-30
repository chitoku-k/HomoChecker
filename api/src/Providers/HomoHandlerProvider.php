<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use HomoChecker\Handlers\ErrorHandler;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Interfaces\ErrorHandlerInterface;

class HomoHandlerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ErrorHandlerInterface::class, ErrorHandler::class);
        $this->app->singleton(ResponseFactoryInterface::class, function (Container $app) {
            /** @var App */
            $slim = $app->make('app');
            return $slim->getResponseFactory();
        });
    }

    public function provides()
    {
        return [
            ErrorHandlerInterface::class,
        ];
    }
}
