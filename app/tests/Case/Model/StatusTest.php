<?php
namespace HomoChecker\Test\Model;

use HomoChecker\Model\Homo;
use HomoChecker\Model\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function testCorrectDomain()
    {
        $screen_name = 'homo';
        $url = 'https://xn--ydko.example.com';
        $icon = 'https://img.example.com';
        $status = 'OK';
        $duration = 1.14514;

        $homo = new Homo;
        $homo->screen_name = $screen_name;
        $homo->url = $url;
        $target = new Status($homo, $icon, $status, $duration);

        $this->assertEquals($screen_name, $target->homo->screen_name);
        $this->assertEquals($url, $target->homo->url);
        $this->assertEquals('ホモ.example.com', $target->homo->display_url);
        $this->assertEquals(true, $target->homo->secure);
        $this->assertEquals($icon, $target->homo->icon);
        $this->assertEquals($status, $target->status);
        $this->assertEquals($duration, $target->duration);
    }

    public function testIncorrectDomain()
    {
        $screen_name = 'homo';
        $url = 'not:a:url';
        $icon = 'https://img.example.com';
        $status = 'CONTAINS';
        $duration = 1.14514;

        $homo = new Homo;
        $homo->screen_name = $screen_name;
        $homo->url = $url;
        $target = new Status($homo, $icon, $status, $duration);

        $this->assertEquals($screen_name, $target->homo->screen_name);
        $this->assertEquals($url, $target->homo->url);
        $this->assertEquals('', $target->homo->display_url);
        $this->assertEquals(false, $target->homo->secure);
        $this->assertEquals($icon, $target->homo->icon);
        $this->assertEquals($status, $target->status);
        $this->assertEquals($duration, $target->duration);
    }
}
