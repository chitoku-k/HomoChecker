<?php
namespace HomoChecker\Model\Validator;

use HomoChecker\Model\Validator\ValidatorBase;

class URLValidator extends ValidatorBase
{
    protected function validate($ch)
    {
        return preg_match(self::TARGET, curl_multi_getcontent($ch)) ? 'CONTAINS' : false;
    }
}
