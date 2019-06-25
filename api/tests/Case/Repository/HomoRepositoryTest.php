<?php
declare(strict_types=1);

namespace HomoChecker\Test\Repository;

use HomoChecker\Repository\HomoRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HomoRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        $this->users = [
            (object) [
                'id' => 1,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
            ],
            (object) [
                'id' => 2,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
            ],
            (object) [
                'id' => 3,
                'screen_name' => 'bar',
                'service' => 'mastodon',
                'url' => 'http://bar.example.com',
            ],
            (object) [
                'id' => 4,
                'screen_name' => 'baz',
                'service' => 'mastodon',
                'url' => 'https://baz.example.com',
            ],
        ];

        $this->usersFoo = [
            (object) [
                'id' => 1,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
            ],
            (object) [
                'id' => 2,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
            ],
        ];
    }

    public function testCount(): void
    {
        $builder = m::mock(Builder::class);
        $builder->shouldReceive('count')
                ->andReturn(4);

        DB::shouldReceive('table')
          ->once()
          ->with('users')
          ->andReturn($builder);

        $homo = new HomoRepository();
        $actual = $homo->count();

        $this->assertEquals(4, $actual);
    }

    public function testCountByScreenName(): void
    {
        $screen_name = 'foo';

        $builder = m::mock(Builder::class);
        $builder->shouldReceive('where->count')
                ->andReturn(2);

        DB::shouldReceive('table')
          ->once()
          ->with('users')
          ->andReturn($builder);

        $homo = new HomoRepository();
        $actual = $homo->countByScreenName($screen_name);

        $this->assertEquals(2, $actual);
    }

    public function testFindAll(): void
    {
        $builder = m::mock(Builder::class);
        $builder->shouldReceive('get->all')
                ->andReturn($this->users);

        DB::shouldReceive('table')
          ->once()
          ->with('users')
          ->andReturn($builder);

        $homo = new HomoRepository();
        $actual = $homo->findAll();

        $this->assertEquals($this->users, $actual);
    }

    public function testFindByScreenName(): void
    {
        $screen_name = 'foo';

        $builder = m::mock(Builder::class);
        $builder->shouldReceive('where->get->all')
                ->andReturn($this->usersFoo);

        DB::shouldReceive('table')
          ->once()
          ->with('users')
          ->andReturn($builder);

        $homo = new HomoRepository();
        $actual = $homo->findByScreenName($screen_name);

        $this->assertEquals($this->usersFoo, $actual);
    }

    public function testExport(): void
    {
        $builder = m::mock(Builder::class);
        $builder->from = 'users';
        $builder->shouldReceive('get')
                ->andReturn(collect($this->users));

        DB::shouldReceive('table')
          ->once()
          ->with('users')
          ->andReturn($builder);

        $sql = <<<'SQL'
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/1');
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/2');
        insert into "users" ("screen_name", "service", "url") values ('bar', 'mastodon', 'http://bar.example.com');
        insert into "users" ("screen_name", "service", "url") values ('baz', 'mastodon', 'https://baz.example.com');
        SQL;

        $homo = new HomoRepository();
        $actual = $homo->export();

        $this->assertEquals($sql, $actual);
    }
}
