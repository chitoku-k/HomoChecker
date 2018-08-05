<?php
declare(strict_types=1);

namespace HomoChecker\Model\Validator;

class ValidatorResult
{
    public const OK = 'OK';
    public const CONTAINS = 'CONTAINS';
    public const WRONG = 'WRONG';
    public const ERROR = 'ERROR';

    private function __construct()
    {
    }
}
