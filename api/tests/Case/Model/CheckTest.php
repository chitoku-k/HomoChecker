<?php
declare(strict_types=1);

namespace HomoChecker\Test\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\Status;
use HomoChecker\Model\Profile\Icon;
use HomoChecker\Model\Validator\HeaderValidator;
use HomoChecker\Model\Validator\DOMValidator;
use HomoChecker\Model\Validator\URLValidator;
use PHPUnit\Framework\TestCase;

class CheckTest extends TestCase
{
    protected function user($screen_name, $url): Homo
    {
        $homo = new Homo();
        $homo->screen_name = $screen_name;
        $homo->url = $url;
        return $homo;
    }

    public function setUp()
    {
        $users = [
            $this->user('foo', 'https://foo.example.com/1'),
            $this->user('foo', 'https://foo.example.com/2'),
            $this->user('bar', 'http://bar.example.com'),
            $this->user('baz', 'https://baz.example.com'),
        ];

        $this->Icon = $this->getMockBuilder(Icon::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->Icon->expects($this->any())
                   ->method('getAsync')
                   ->will($this->returnCallback(function ($sn) { return new FulfilledPromise($sn); }));

        $this->Homo = $this->createMock(Homo::class);
        $this->Homo->expects($this->any())
                   ->method('find')
                   ->will($this->returnValue($users));

        $regex = '|https?://homo\.example\.com/?|';
        $this->validators = [
            new HeaderValidator($regex),
            new DOMValidator($regex),
            new URLValidator($regex),
        ];
    }

    public function testExecuteAsync(): void
    {
        $this->Client = new Client([
            'allow_redirects' => false,
            'handler' => HandlerStack::create(new MockHandler([
                // 'https://foo.example.com/1' (1/1)
                new Response(301, ['Location' => 'https://homo.example.com'], ''),
                // 'https://foo.example.com/2' (1/2)
                new Response(302, ['Location' => 'https://foo2.example.com'], ''),
                // 'http://bar.example.com' (1/1)
                new Response(200, [], '
                    <!doctype html>
                    <title>Success</title>
                    <meta http-equiv="refresh" content="0; https://homo.example.com">
                '),
                // 'https://baz.example.com' (1/1)
                new RequestException('Connection error', new Request('GET', '')),
                // 'https://foo2.example.com' (2/2)
                new Response(200, [], '
                    <!doctype html>
                    <title>Fail</title>
                '),
            ])),
        ]);
        $this->Check = new Check($this->Client, $this->Homo, $this->Icon, ...$this->validators);

        $expected = [
            new Status($this->user('foo', 'https://foo.example.com/1'), 'foo', 'OK', 0),
            new Status($this->user('foo', 'https://foo.example.com/2'), 'foo', 'WRONG', 0),
            new Status($this->user('bar', 'http://bar.example.com'), 'bar', 'OK', 0),
            new Status($this->user('baz', 'https://baz.example.com'), 'baz', 'ERROR', 0),
        ];

        $actual = $this->Check->execute(null, function ($status) use ($expected) {
            $this->assertInstanceOf(Status::class, $status);
            $this->assertContains($status, $expected, false, false, false);
        });

        $this->assertContainsOnlyInstancesOf(Status::class, $actual);
        $this->assertArraySubset($expected, $actual);
    }
}
