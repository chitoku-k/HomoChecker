<?php
declare(strict_types=1);

namespace HomoChecker\Handlers;

use HomoChecker\Http\ErrorResponse;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpSpecializedException;
use Slim\Interfaces\ErrorHandlerInterface;

final class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory) {}

    #[\Override]
    public function __invoke(Request $request, \Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): Response
    {
        if (!$exception instanceof HttpSpecializedException) {
            Log::error($exception);
            $exception = new HttpInternalServerErrorException($request, $exception->getMessage());
        }

        /** @var ErrorResponse $response */
        $response = $this->responseFactory->createResponse($exception->getCode());
        return $response->withJson([
            'errors' => [
                [
                    'code' => $response->getStatusCode(),
                    'message' => $response->getReasonPhrase(),
                ],
            ],
        ]);
    }
}
