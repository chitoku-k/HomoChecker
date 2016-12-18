<?php
namespace HomoChecker\Test\Model\Validator;

use GuzzleHttp\Psr7\Response;
use HomoChecker\Model\Validator\DOMValidator;
use HomoChecker\Test\Mock\Utilities\MockContainer;
use PHPUnit\Framework\TestCase;

class DOMValidatorTest extends TestCase
{
    public function setUp()
    {
        $this->container = new MockContainer;
    }

    public function testValidate()
    {
        $validator = new DOMValidator($this->container);
        $this->container->target = '|https?://example\.com/?|';
        $this->assertEquals('OK', $validator(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>This should pass validation</title>
                    <meta http-equiv="refresh" content="0; https://example.com">
                </head>
                <body>
                </body>
            </html>
        ')));
        $this->assertFalse($validator(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>This should not pass validation</title>
                    <meta http-equiv="refresh" content="0; https://homo.example.com">
                </head>
                <body>
                </body>
            </html>
        ')));
        $this->assertFalse($validator(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>This does not contain refresh</title>
                </head>
                <body>
                </body>
            </html>
        ')));
        $this->assertFalse($validator(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>This is invalid but should not trigger error</title>
                    <body>
                </head>
                    </body>
            </html>
        ')));
    }
}
