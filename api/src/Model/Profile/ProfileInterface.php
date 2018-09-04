<?php
declare(strict_types=1);

namespace HomoChecker\Model\Profile;

use GuzzleHttp\Promise;

interface ProfileInterface
{
    public function getIconAsync(string $screen_name): Promise\PromiseInterface;
    public function getDefaultUrl(): string;
    public function getServiceName(): string;
}
