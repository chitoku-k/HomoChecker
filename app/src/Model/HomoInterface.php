<?php
namespace HomoChecker\Model;

interface HomoInterface
{
    function find(array $where = []): array;
}
