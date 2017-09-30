<?php
declare(strict_types=1);

namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Validator\ValidatorBase;

class HeaderValidator extends ValidatorBase
{
    protected function validate(Response $response)
    {
        $url = $response->getHeaderLine('Location');
        return preg_match($this->regex, $url) ? 'OK' : false;
    }
}
