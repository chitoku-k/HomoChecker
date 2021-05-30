<?php
declare(strict_types=1);

namespace HomoChecker\Test\Domain;

use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Result;
use HomoChecker\Domain\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function testConstruct(): void
    {
        $screen_name = 'homo';
        $service = 'twitter';
        $url = 'https://xn--ydko.example.com';
        $icon = 'https://img.example.com';
        $status = 'OK';
        $ip = '2001:db8::4545:1';
        $duration = 1.14514;

        $homo = new Homo(compact(
            'screen_name',
            'service',
            'url',
        ));

        $result = new Result(compact(
            'status',
            'ip',
            'url',
            'duration',
        ));

        $actual = new Status(compact(
            'homo',
            'result',
            'icon',
        ));

        $this->assertEquals($homo, $actual->getHomo());
        $this->assertEquals($result, $actual->getResult());
        $this->assertEquals($icon, $actual->getIcon());
    }

    public function testCorrectDomain(): void
    {
        $screen_name = 'homo';
        $service = 'twitter';
        $url = 'https://xn--ydko.example.com';
        $icon = 'https://img.example.com';
        $status = 'OK';
        $ip = '2001:db8::4545:1';
        $duration = 1.14514;

        $homo = new Homo(compact(
            'screen_name',
            'service',
            'url',
        ));

        $result = new Result(compact(
            'status',
            'ip',
            'url',
            'duration',
        ));

        $actual = new Status(compact(
            'homo',
            'result',
            'icon',
        ));

        $this->assertEquals($screen_name, $actual->getHomo()->getScreenName());
        $this->assertEquals($service, $actual->getHomo()->getService());
        $this->assertEquals($url, $actual->getHomo()->getUrl());
        $this->assertEquals($screen_name, $actual->getHomoArray()['screen_name']);
        $this->assertEquals($service, $actual->getHomoArray()['service']);
        $this->assertEquals($url, $actual->getHomoArray()['url']);
        $this->assertEquals('ホモ.example.com', $actual->getHomoArray()['display_url']);
        $this->assertEquals(true, $actual->getHomoArray()['secure']);
        $this->assertEquals($icon, $actual->getIcon());
        $this->assertEquals($status, $actual->getResultArray()['status']);
        $this->assertEquals($ip, $actual->getResultArray()['ip']);
        $this->assertEquals('https://ホモ.example.com', $actual->getResultArray()['url']);
        $this->assertEquals($duration, $actual->getResultArray()['duration']);
    }

    public function testIncorrectDomain(): void
    {
        $screen_name = 'homo';
        $service = 'twitter';
        $url = 'not:a:url';
        $icon = 'https://img.example.com';
        $status = 'CONTAINS';
        $ip = '2001:db8::4545:1';
        $duration = 1.14514;

        $homo = new Homo(compact(
            'screen_name',
            'service',
            'url',
        ));

        $result = new Result(compact(
            'status',
            'ip',
            'url',
            'duration',
        ));

        $actual = new Status(compact(
            'homo',
            'result',
            'icon',
        ));

        $this->assertEquals($screen_name, $actual->getHomo()->getScreenName());
        $this->assertEquals($service, $actual->getHomo()->getService());
        $this->assertEquals($url, $actual->getHomo()->getUrl());
        $this->assertEquals($screen_name, $actual->getHomoArray()['screen_name']);
        $this->assertEquals($service, $actual->getHomoArray()['service']);
        $this->assertEquals($url, $actual->getHomoArray()['url']);
        $this->assertEquals('', $actual->getHomoArray()['display_url']);
        $this->assertEquals(false, $actual->getHomoArray()['secure']);
        $this->assertEquals($status, $actual->getResultArray()['status']);
        $this->assertEquals($ip, $actual->getResultArray()['ip']);
        $this->assertEquals('', $actual->getResultArray()['url']);
        $this->assertEquals($duration, $actual->getResultArray()['duration']);
    }

    public function testIncorrectURL(): void
    {
        $screen_name = 'homo';
        $service = 'twitter';
        $url = null;
        $icon = 'https://img.example.com';
        $status = 'CONTAINS';
        $ip = '2001:db8::4545:1';
        $duration = 1.14514;

        $homo = new Homo(compact(
            'screen_name',
            'service',
            'url',
        ));

        $result = new Result(compact(
            'status',
            'ip',
            'url',
            'duration',
        ));

        $actual = new Status(compact(
            'homo',
            'result',
            'icon',
        ));

        $this->assertEquals($screen_name, $actual->getHomo()->getScreenName());
        $this->assertEquals($service, $actual->getHomo()->getService());
        $this->assertEquals($url, $actual->getHomo()->getUrl());
        $this->assertEquals($screen_name, $actual->getHomoArray()['screen_name']);
        $this->assertEquals($service, $actual->getHomoArray()['service']);
        $this->assertEquals($url, $actual->getHomoArray()['url']);
        $this->assertEquals('', $actual->getHomoArray()['display_url']);
        $this->assertEquals(false, $actual->getHomoArray()['secure']);
        $this->assertEquals($status, $actual->getResultArray()['status']);
        $this->assertEquals($ip, $actual->getResultArray()['ip']);
        $this->assertEquals('', $actual->getResultArray()['url']);
        $this->assertEquals($duration, $actual->getResultArray()['duration']);
    }
}
