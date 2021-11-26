<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service\Client;

use HomoChecker\Contracts\Service\Client\Altsvc;
use PHPUnit\Framework\TestCase;

class AltsvcTest extends TestCase
{
    public function testConstructClear(): void
    {
        $actual = new Altsvc('clear');

        $this->assertTrue($actual->isClear());
    }

    public function testConstructProtocol(): void
    {
        $actual = new Altsvc('h3=":443"; ma=86400; persist');

        $this->assertEquals('h3', $actual->getProtocolId());
        $this->assertEquals(':443', $actual->getAltAuthority());
        $this->assertEquals(86400, $actual->getMaxAge());
    }
}
