<?php
declare(strict_types=1);

namespace HomoChecker;

use Illuminate\Container\Container;

final class Application extends Container
{
    /**
     * Determine if the application is running in the console.
     * @return bool
     */
    public function runningInConsole()
    {
        return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }
}
