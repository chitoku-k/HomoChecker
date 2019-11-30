<?php
declare(strict_types=1);

namespace HomoChecker\Service\Validator;

use HomoChecker\Contracts\Service\ValidatorService as ValidatorServiceContract;
use HomoChecker\Domain\Validator\ValidationResult;
use Psr\Http\Message\ResponseInterface as Response;

class URLValidatorService implements ValidatorServiceContract
{
    protected string $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Response $response)
    {
        return preg_match($this->regex, (string) $response->getBody()) ? ValidationResult::CONTAINS : false;
    }
}
