<?php
namespace HomoChecker\Test\Model\Validator;

use GuzzleHttp\Psr7\Response;
use HomoChecker\Model\Validator\HeaderValidator;
use HomoChecker\Test\Mock\Utilities\MockContainer;
use PHPUnit\Framework\TestCase;

class HeaderValidatorTest extends TestCase
{
    public function setUp()
    {
        $this->container = new MockContainer;
    }

    public function testValidate()
    {
        $validator = new HeaderValidator($this->container);
        $this->container->target = '|https?://example\.com/?|';
        $this->assertEquals('OK', $validator(new Response(301, ['Location' => 'http://example.com'])));
        $this->assertEquals('OK', $validator(new Response(302, ['Location' => 'http://example.com'])));
        $this->assertFalse($validator(new Response(200)));
        $this->assertFalse($validator(new Response(301, ['Redirect' => 'http://example.com'])));
        $this->assertFalse($validator(new Response(301, ['Location' => 'http://homo.example.com'])));
    }
}
