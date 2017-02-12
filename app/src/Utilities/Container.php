<?php
namespace HomoChecker\Utilities;

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

        foreach (self::ERRORS as $type => $code) {
            $this[$type] = function () use ($code) {
                return function (Request $request, Response $response, $e = null) use ($code) {
                    error_log((string)$e);
                    return $this->onError($code, $request, $response);
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
