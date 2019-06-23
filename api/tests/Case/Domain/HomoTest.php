<?php
declare(strict_types=1);

namespace HomoChecker\Test\Domain;

use HomoChecker\Domain\Homo;
use PHPUnit\Framework\TestCase;

class HomoTest extends TestCase
{
    public function testConstruct(): void
    {
        $id = 1;
        $screen_name = 'homo';
        $service = 'twitter';
        $url = 'https://xn--ydko.example.com';

        $actual = new Homo(compact(
            'id',
            'screen_name',
            'service',
            'url',
        ));

        $this->assertEquals($id, $actual->getId());
        $this->assertEquals($screen_name, $actual->getScreenName());
        $this->assertEquals($service, $actual->getService());
        $this->assertEquals($url, $actual->getUrl());
    }
}
