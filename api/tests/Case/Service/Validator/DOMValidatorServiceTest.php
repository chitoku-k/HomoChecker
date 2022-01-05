<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service\Validator;

use GuzzleHttp\Psr7\Response;
use HomoChecker\Domain\Validator\ValidationResult;
use HomoChecker\Service\Validator\DOMValidatorService;
use PHPUnit\Framework\TestCase;

class DOMValidatorServiceTest extends TestCase
{
    public function testValidate(): void
    {
        $validator = new DOMValidatorService('|https?://example\.com/?|');
        $this->assertEquals(ValidationResult::OK, $validator->validate(new Response(200, [], '
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
        $this->assertEquals(ValidationResult::OK, $validator->validate(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>Case-insensitive match</title>
                    <Meta Http-Equiv="Refresh" Content="0; https://example.com">
                </head>
                <body>
                </body>
            </html>
        ')));
        $this->assertFalse($validator->validate(new Response(200, [], '
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
        $this->assertFalse($validator->validate(new Response(200, [], '
            <!doctype html>
            <html>
                <head>
                    <title>This does not contain refresh</title>
                </head>
                <body>
                </body>
            </html>
        ')));
        $this->assertFalse($validator->validate(new Response(200, [], '
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
