<?php
namespace HomoChecker\Model\Validator;

use HomoChecker\Model\ValidatorBase;

class DOMValidator extends ValidatorBase
{
    protected function validate($ch, string $body)
    {
        $doc = new \DOMDocument;
        @$doc->loadHTML($body);
        $xpath = new \DOMXPath($doc);
        $url = $xpath->evaluate('string(//meta[@http-equiv="refresh"]/@content)');
        return preg_match(self::TARGET, $url) ? 'OK' : false;
    }
}
