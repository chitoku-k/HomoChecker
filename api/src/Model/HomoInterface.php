<?php
namespace HomoChecker\Model;

interface HomoInterface
{
    public function find(array $where = []): array;
}
