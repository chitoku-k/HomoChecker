<?php
declare(strict_types=1);

namespace HomoChecker\Http\Factory;

use HomoChecker\Http\ErrorResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ErrorResponseFactory implements ResponseFactoryInterface
{
    public function __construct(protected ResponseFactoryInterface $responseFactory, protected StreamFactoryInterface $streamFactory)
    {
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($code, $reasonPhrase);
        return new ErrorResponse($response, $this->streamFactory);
    }
}
