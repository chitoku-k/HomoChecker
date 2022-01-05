<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service;

use HomoChecker\Domain\Validator\ValidationResult;
use Psr\Http\Message\ResponseInterface as Response;

interface ValidatorService
{
    /**
     * Return the result of validation.
     * @param  Response              $response Response.
     * @return bool|ValidationResult The result.
     */
    public function validate(Response $response);
}
