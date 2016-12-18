<?php
namespace HomoChecker\Test\Model\Validator;

use GuzzleHttp\Psr7\Response;
use HomoChecker\Model\Validator\URLValidator;
use HomoChecker\Test\Mock\Utilities\MockContainer;
use PHPUnit\Framework\TestCase;

class URLValidatorTest extends TestCase
{
    public function setUp()
    {
        $this->container = new MockContainer;
    }

    public function testValidate()
    {
        $validator = new URLValidator($this->container);
        $this->container->target = '|https?://example\.com/?|';
        $this->assertEquals('CONTAINS', $validator(new Response(200, [], '
            We love https://example.com!
        ')));
        $this->assertEquals('CONTAINS', $validator(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>This should pass validation</title>
                </head>
                <body>
                    <script>
                        location.href = "https://example.com";
                    </script>
                </body>
            </html>
        ')));
        $this->assertFalse($validator(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>This should not pass validation</title>
                </head>
                <body>
                </body>
            </html>
        ')));
    }
}
