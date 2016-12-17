<?php
namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Validator\ValidatorBase;

class URLValidator extends ValidatorBase
{
    protected function validate(Response $response)
    {
        return preg_match(self::TARGET, (string)$response->getBody()) ? 'CONTAINS' : false;
    }
}
