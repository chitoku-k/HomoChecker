<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service\Validator;

use GuzzleHttp\Psr7\Response;
use HomoChecker\Service\Validator\HeaderValidatorService;
use PHPUnit\Framework\TestCase;

class HeaderValidatorServiceTest extends TestCase
{
    public function testValidate(): void
    {
        $validator = new HeaderValidatorService('|https?://example\.com/?|');
        $this->assertEquals('OK', $validator->validate(new Response(301, ['Location' => 'http://example.com'])));
        $this->assertEquals('OK', $validator->validate(new Response(302, ['Location' => 'http://example.com'])));
        $this->assertFalse($validator->validate(new Response(200)));
        $this->assertFalse($validator->validate(new Response(301, ['Redirect' => 'http://example.com'])));
        $this->assertFalse($validator->validate(new Response(301, ['Location' => 'http://homo.example.com'])));
    }
}
