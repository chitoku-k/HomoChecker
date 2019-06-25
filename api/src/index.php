<?php
declare(strict_types=1);

namespace HomoChecker;

use HomoChecker\Providers\HomoProvider;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->singleton('settings', function () {
    return require __DIR__ . '/config.php';
});

(new HomoProvider($container))->register();
(new HomoProvider($container))->boot();

Facade::clearResolvedInstances();
Facade::setFacadeApplication($container);

$container->make('app')->run();
