<?php
namespace HomoChecker;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Container extends \Slim\Container
{
    const ERRORS = [
        'notFoundHandler'   => 404,
        'notAllowedHandler' => 405,
        'errorHandler'      => 500,
        'phpErrorHandler'   => 500,
    ];

    public function __construct()
    {
        parent::__construct([
            'settings' => [
                'outputBuffering'        => false,
                'addContentLengthHeader' => false,
            ],
        ]);

        $this->registerHandlers();
    }

    protected function registerHandlers()
    {
        foreach (self::ERRORS as $type => $code) {
            $this[$type] = function () use ($code) {
                return function (...$params) use ($code) {
                    return $this->onError($code, ...$params);
                };
            };
        }
    }

    protected function onError(int $code, Request $request, Response $response): Response
    {
        $response = $response->withStatus($code);
        return $response->withJson([
            'errors' => [
                [
                    'code'    => $response->getStatusCode(),
                    'message' => $response->getReasonPhrase(),
                ],
            ],
        ]);
    }
}
