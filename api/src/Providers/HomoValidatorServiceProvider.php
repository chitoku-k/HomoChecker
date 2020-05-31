<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use HomoChecker\Service\Validator\DOMValidatorService;
use HomoChecker\Service\Validator\HeaderValidatorService;
use HomoChecker\Service\Validator\URLValidatorService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class HomoValidatorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('validators', fn (Container $app) => collect([
            new HeaderValidatorService($app->make('config')->get('regex')),
            new DOMValidatorService($app->make('config')->get('regex')),
            new URLValidatorService($app->make('config')->get('regex')),
        ]));
    }

    public function provides()
    {
        return [
            'validators',
        ];
    }
}
