<?php
declare(strict_types=1);

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

    /**
     * Return the result of validation.
     * @param  Response $response Response.
     * @return string|bool The result.
     */
    abstract protected function validate(Response $response);
}
