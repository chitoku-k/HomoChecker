<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service\Validator;

use GuzzleHttp\Psr7\Response;
use HomoChecker\Domain\Validator\ValidationResult;
use HomoChecker\Service\Validator\URLValidatorService;
use PHPUnit\Framework\TestCase;

class URLValidatorServiceTest extends TestCase
{
    public function testValidate(): void
    {
        $validator = new URLValidatorService('|https?://example\.com/?|');
        $this->assertEquals(ValidationResult::CONTAINS, $validator->validate(new Response(200, [], '
            We love https://example.com!
        ')));
        $this->assertEquals(ValidationResult::CONTAINS, $validator->validate(new Response(200, [], '
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
        $this->assertFalse($validator->validate(new Response(200, [], '
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
