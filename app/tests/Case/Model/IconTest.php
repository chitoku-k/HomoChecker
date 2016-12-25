<?php
namespace HomoChecker\Test\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HomoChecker\Model\Icon;
use PHPUnit\Framework\TestCase;

class IconTest extends TestCase
{
    public function testGetAsync()
    {
        $url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], "
                <!doctype html>
                <html>
                    <head>
                        <title>This contains icon URL</title>
                    </head>
                    <body>
                        <img src='{$url}'>
                    </body>
                </html>
            "),
            new Response(200, [], '
                <!doctype html>
                <html>
                    <head>
                        <title>This does not contain icon URL</title>
                    </head>
                    <body>
                    </body>
                </html>
            '),
            new Response(404, [], "
                <!doctype html>
                <html>
                    <head>
                        <title>This returns 404</title>
                    </head>
                    <body>
                        <img src='{$url}'>
                    </body>
                </html>
            "),
            new RequestException('Connection problem occurred', new Request('GET', '')),
        ]));

        $client = new Client(compact('handler'));

        $icon = new Icon($client);
        $this->assertEquals($url, $icon->getAsync('example')->wait());
        $this->assertEquals($icon::$default, $icon->getAsync('example')->wait());
        $this->assertEquals($icon::$default, $icon->getAsync('example')->wait());
        $this->assertEquals($icon::$default, $icon->getAsync('example')->wait());
    }
}
