<?php
namespace HomoChecker\Model;

abstract class ValidatorBase {
    const TARGET = '/https?:\/\/twitter\.com\/mpyw\/?/';

    public function __invoke($ch, string $body) {
        $this->validate($ch, $body);
    }

    protected abstract function validate($ch, string $body);
}
