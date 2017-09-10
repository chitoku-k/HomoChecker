<?php
namespace HomoChecker\Model;

use GuzzleHttp\Promise;

interface IconInterface
{
    public function getAsync(string $screen_name): Promise\PromiseInterface;
}
