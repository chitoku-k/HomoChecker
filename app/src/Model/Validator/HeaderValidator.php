<?php
namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Validator\ValidatorBase;

class HeaderValidator extends ValidatorBase
{
    protected function validate(Response $response)
    {
        $url = $response->getHeaderLine('Location');
        return preg_match(self::TARGET, $url) ? 'OK' : false;
    }
}
