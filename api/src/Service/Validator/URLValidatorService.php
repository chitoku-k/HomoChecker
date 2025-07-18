<?php
declare(strict_types=1);

namespace HomoChecker\Service\Validator;

use HomoChecker\Contracts\Service\ValidatorService as ValidatorServiceContract;
use HomoChecker\Domain\Validator\ValidationResult;
use Psr\Http\Message\ResponseInterface as Response;

final class URLValidatorService implements ValidatorServiceContract
{
    /**
     * @param non-empty-string $regex
     */
    public function __construct(private string $regex) {}

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function validate(Response $response): false|ValidationResult
    {
        return preg_match($this->regex, (string) $response->getBody()) ? ValidationResult::CONTAINS : false;
    }
}
