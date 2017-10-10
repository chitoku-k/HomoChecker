<?php
declare(strict_types=1);

namespace HomoChecker\Model;

use GuzzleHttp\Promise;

interface ProfileInterface
{
    public function getAsync(string $screen_name): Promise\PromiseInterface;
}
