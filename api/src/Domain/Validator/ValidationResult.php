<?php
declare(strict_types=1);

namespace HomoChecker\Domain\Validator;

class ValidationResult
{
    public const OK = 'OK';
    public const CONTAINS = 'CONTAINS';
    public const WRONG = 'WRONG';
    public const ERROR = 'ERROR';

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
