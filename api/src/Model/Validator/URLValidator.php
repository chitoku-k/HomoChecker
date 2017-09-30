<?php
declare(strict_types=1);

namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Validator\ValidatorBase;

class URLValidator extends ValidatorBase
{
    /**
     * {@inheritdoc}
     */
    protected function validate(Response $response)
    {
        return preg_match($this->regex, (string)$response->getBody()) ? 'CONTAINS' : false;
    }
}
