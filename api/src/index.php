<?php
declare(strict_types=1);

namespace HomoChecker;

use HomoChecker\Providers\HomoProvider;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Facades\Facade;

require __DIR__ . '/../vendor/autoload.php';

/** @var ApplicationContract $application */
$application = new Application();
$application->singleton('settings', fn () => require __DIR__ . '/config.php');

(new HomoProvider($application))->register();

Facade::clearResolvedInstances();
Facade::setFacadeApplication($application);

$application->make('app')->run();
