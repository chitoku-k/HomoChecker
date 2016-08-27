<?php
namespace HomoChecker\Model;

abstract class ValidatorBase
{
    const TARGET = '/https?:\/\/twitter\.com\/mpyw\/?/';

    public function __invoke($ch)
    {
        return $this->validate($ch);
    }

    abstract protected function validate($ch);
}
