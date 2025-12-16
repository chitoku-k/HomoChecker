<?php
declare(strict_types=1);

namespace HomoChecker\Test;

use HomoChecker\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testRunningInConsole(): void
    {
        $application = new Application();

        $this->assertTrue($application->runningInConsole());
    }
}
