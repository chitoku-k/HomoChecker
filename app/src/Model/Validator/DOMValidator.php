<?php
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
        $url = $xpath->evaluate('string(//meta[@http-equiv="refresh"]/@content)');
        return preg_match($this->container->target, $url) ? 'OK' : false;
    }
}
