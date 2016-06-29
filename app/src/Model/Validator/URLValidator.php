<?php
namespace HomoChecker\Model\Validator;

use HomoChecker\Model\ValidatorBase;

class URLValidator extends ValidatorBase {
    protected function validate($ch, string $body) {
        return preg_match(self::TARGET, $body) ? 'CONTAINS' : false;
    }
}

