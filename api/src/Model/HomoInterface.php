<?php
declare(strict_types=1);

namespace HomoChecker\Model;

interface HomoInterface
{
    public function find(array $where = []): array;
}
