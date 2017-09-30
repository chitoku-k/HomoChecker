<?php
declare(strict_types=1);

namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Validator\ValidatorBase;

class DOMValidator extends ValidatorBase
{
    protected function validate(Response $response)
    {
        $doc = new \DOMDocument;
        @$doc->loadHTML((string)$response->getBody());
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('php', 'http://php.net/xpath');
        $xpath->registerPhpFunctions();
        $url = $xpath->evaluate('string(//meta[contains(php:functionString("strtolower", @http-equiv), "refresh")]/@content)');
        return preg_match($this->regex, $url) ? 'OK' : false;
    }
}
