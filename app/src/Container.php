<?php
namespace HomoChecker;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\Proxy;
use HomoChecker\Model\Check;
use HomoChecker\Model\Icon;
use HomoChecker\Model\Validator\HeaderValidator;
use HomoChecker\Model\Validator\DOMValidator;
use HomoChecker\Model\Validator\URLValidator;
use HomoChecker\Utilities\RawCurlFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Container extends \Slim\Container
{
    const TIMEOUT = 5;
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
        $this->checker = new Check($this);
        $this->icon = new Icon($this);
        $this->validators = [
            new HeaderValidator,
            new DOMValidator,
            new URLValidator,
        ];
        $this->client = new Client([
            'handler' => Proxy::wrapSync(new CurlMultiHandler([
                'handle_factory' => new RawCurlFactory(50),
            ]), new CurlHandler()),
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
