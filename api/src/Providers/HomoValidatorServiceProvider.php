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
        $this->app->when([HeaderValidatorService::class, DOMValidatorService::class, URLValidatorService::class])
            ->needs('$regex')
            ->giveConfig('regex');

        $this->app->singleton('validators', fn (Container $app) => collect([
            $app->make(HeaderValidatorService::class),
            $app->make(DOMValidatorService::class),
            $app->make(URLValidatorService::class),
        ]));
    }

    public function provides()
    {
        return [
            'validators',
        ];
    }
}
