<?php
namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;

abstract class ValidatorBase
{
    const TARGET = '/https?:\/\/twitter\.com\/mpyw\/?/';

    public function __invoke(Response $response)
    {
        return $this->validate($response);
    }

    abstract protected function validate(Response $response);
}
