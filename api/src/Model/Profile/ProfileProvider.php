<?php
declare(strict_types=1);

namespace HomoChecker\Model\Profile;

class ProfileProvider
{
    public function __construct(Profile ...$profiles)
    {
        foreach ($profiles as $profile) {
            $this->{$profile->getServiceName()} = $profile;
        }
    }
}
