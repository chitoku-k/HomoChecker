<?php
declare(strict_types=1);

namespace HomoChecker\Test\Domain;

use HomoChecker\Domain\Profile;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    public function testConstruct(): void
    {
        $icon_url = 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg';

        $actual = new Profile(compact(
            'icon_url',
        ));

        $this->assertEquals($icon_url, $actual->getIconUrl());
    }
}
