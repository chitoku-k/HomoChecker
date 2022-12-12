<?php
declare(strict_types=1);

namespace HomoChecker\Test\Repository;

use HomoChecker\Repository\AltsvcRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class AltsvcRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        Facade::clearResolvedInstances();

        $this->altsvcs = [
            (object) [
                'url' => 'http://foo.example.com/1',
                'protocol' => 'h3',
                'expires_at' => '2022-12-31 23:59:59',
            ],
            (object) [
                'url' => 'https://homo.example.com',
                'protocol' => 'h3',
                'expires_at' => '2022-12-31 23:59:59',
            ],
        ];
    }

    public function testFindAll(): void
    {
        /** @var Builder&MockInterface $builder */
        $builder = m::mock(Builder::class);
        $builder->shouldReceive('whereRaw->get->all')
                ->andReturn($this->altsvcs);

        DB::shouldReceive('table')
          ->once()
          ->with('altsvcs')
          ->andReturn($builder);

        $altsvc = new AltsvcRepository();
        $actual = $altsvc->findAll();

        $this->assertEquals($this->altsvcs, $actual);
    }

    public function testSave(): void
    {
        /** @var Builder&MockInterface $builder */
        $builder = m::mock(Builder::class);
        $builder->shouldReceive('upsert')
                ->withArgs([
                    [
                        [
                            'url' => 'https://homo.example.com',
                            'protocol' => 'h3',
                            'expires_at' => '2022-12-31 23:59:59',
                        ],
                    ],
                    ['url'],
                    ['protocol', 'expires_at'],
                ]);

        DB::shouldReceive('table')
          ->once()
          ->with('altsvcs')
          ->andReturn($builder);

        $altsvc = new AltsvcRepository();
        $altsvc->save('https://homo.example.com', 'h3', '2022-12-31 23:59:59');
    }
}
