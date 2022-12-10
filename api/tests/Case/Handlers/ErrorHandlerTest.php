<?php
declare(strict_types=1);

namespace HomoChecker\Test\Handlers;

use HomoChecker\Handlers\ErrorHandler;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Http\Factory\DecoratedResponseFactory;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class ErrorHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        parent::setUp();
        Facade::clearResolvedInstances();
    }

    public function testInternalServerError(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/healthz');
        $e = new \Exception('Internal Server Error');

        Log::shouldReceive('error')
            ->once()
            ->withArgs([$e]);

        $action = new ErrorHandler(new DecoratedResponseFactory(new ResponseFactory(), new StreamFactory()));

        $response = $action(
            $request,
            $e,
            false,
            false,
            false,
        );

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/json|', $actual);

        $actual = (string) $response->getBody();
        $errors = [
            'errors' => [
                [
                    'code' => 500,
                    'message' => 'Internal Server Error',
                ],
            ],
        ];

        $expected = json_encode($errors);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }
}
