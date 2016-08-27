<?php
namespace HomoChecker\Model\Validator;

use HomoChecker\Model\ValidatorBase;

class HeaderValidator extends ValidatorBase
{
    protected function validate($ch)
    {
        $url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        return preg_match(self::TARGET, $url) ? 'OK' : false;
    }
}
