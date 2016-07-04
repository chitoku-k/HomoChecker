<?php
namespace HomoChecker\Model;

abstract class ValidatorBase
{
    const TARGET = '/https?:\/\/twitter\.com\/mpyw\/?/';

    public function __invoke($ch, string $body)
    {
        return $this->validate($ch, $body);
    }

    abstract protected function validate($ch, string $body);
}
