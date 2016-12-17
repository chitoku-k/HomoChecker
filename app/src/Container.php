<?php
namespace HomoChecker;

use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Container extends \Slim\Container
{
    const TIMEOUT = 5000;
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
        $this->client = new Client([
            'timeout' => self::TIMEOUT,
            'allow_redirects' => false,
            'headers' => [
                'User-Agent' => 'Homozilla/5.0 (Checker/1.14.514; homOSeX 8.10)',
            ],
        ]);
    }

    protected function registerHandlers()
    {
        foreach (self::ERRORS as $type => $code) {
            $this[$type] = function () use ($code) {
                return function (Request $request, Response $response, $e) use ($code) {
                    error_log((string)$e);
                    return $this->onError($code, $request, $response, $e);
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
