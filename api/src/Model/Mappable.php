<?php
declare(strict_types=1);

namespace HomoChecker\Model;

interface Mappable
{
    public function find($where = []): array;
}
