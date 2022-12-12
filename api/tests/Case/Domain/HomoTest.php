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
        $icon_url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';

        $actual = new Homo(compact(
            'id',
            'screen_name',
            'service',
            'url',
            'icon_url',
        ));

        $this->assertEquals($id, $actual->getId());
        $this->assertEquals($screen_name, $actual->getScreenName());
        $this->assertEquals($service, $actual->getService());
        $this->assertEquals($url, $actual->getUrl());

        $this->assertNotNull($actual->getProfile());
        $this->assertEquals($icon_url, $actual->getProfile()->getIconUrl());
    }

    public function testConstructWithoutProfile(): void
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

        $this->assertNull($actual->getProfile());
    }
}
