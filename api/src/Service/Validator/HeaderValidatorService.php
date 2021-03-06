<?php
declare(strict_types=1);

namespace HomoChecker\Service\Validator;

use HomoChecker\Contracts\Service\ValidatorService as ValidatorServiceContract;
use HomoChecker\Domain\Validator\ValidationResult;
use Psr\Http\Message\ResponseInterface as Response;

class HeaderValidatorService implements ValidatorServiceContract
{
    public function __construct(protected string $regex)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Response $response)
    {
        $url = $response->getHeaderLine('Location');

        return preg_match($this->regex, $url) ? ValidationResult::OK : false;
    }
}
