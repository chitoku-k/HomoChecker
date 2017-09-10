<?php
namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;

abstract class ValidatorBase implements ValidatorInterface
{
    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    public function __invoke(Response $response)
    {
        return $this->validate($response);
    }

    abstract protected function validate(Response $response);
}
