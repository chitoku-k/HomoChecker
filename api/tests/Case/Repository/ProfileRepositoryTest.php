<?php
declare(strict_types=1);

namespace HomoChecker\Test\Repository;

use HomoChecker\Repository\ProfileRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ProfileRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        Facade::clearResolvedInstances();
    }

    public function testSave(): void
    {
        /** @var Builder&MockInterface $builder */
        $builder = m::mock(Builder::class);
        $builder->shouldReceive('upsert')
                ->withArgs([
                    [
                        [
                            'screen_name' => 'foo',
                            'icon_url' => 'https://img.example.com/foo',
                            'expires_at' => '2022-12-31 23:59:59',
                        ],
                    ],
                    ['screen_name'],
                    ['icon_url', 'expires_at'],
                ]);

        DB::shouldReceive('table')
          ->once()
          ->with('profiles')
          ->andReturn($builder);

        $altsvc = new ProfileRepository();
        $altsvc->save('foo', 'https://img.example.com/foo', '2022-12-31 23:59:59');
    }
}
