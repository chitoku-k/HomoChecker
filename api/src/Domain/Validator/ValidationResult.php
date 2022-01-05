<?php
declare(strict_types=1);

namespace HomoChecker\Domain\Validator;

use JsonSerializable;

enum ValidationResult: string implements JsonSerializable
{
    case OK = 'OK';
    case CONTAINS = 'CONTAINS';
    case WRONG = 'WRONG';
    case ERROR = 'ERROR';

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
