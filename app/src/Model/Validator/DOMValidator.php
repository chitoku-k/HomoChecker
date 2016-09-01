<?php
namespace HomoChecker\Model\Validator;

use HomoChecker\Model\Validator\ValidatorBase;

class DOMValidator extends ValidatorBase
{
    protected function validate($ch)
    {
        $doc = new \DOMDocument;
        @$doc->loadHTML(curl_multi_getcontent($ch));
        $xpath = new \DOMXPath($doc);
        $url = $xpath->evaluate('string(//meta[@http-equiv="refresh"]/@content)');
        return preg_match(self::TARGET, $url) ? 'OK' : false;
    }
}
