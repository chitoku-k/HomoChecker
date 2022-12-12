<?php
declare(strict_types=1);

namespace HomoChecker\Test\Repository;

use HomoChecker\Repository\HomoRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class HomoRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        Facade::clearResolvedInstances();

        $this->users = [
            (object) [
                'id' => 1,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
                'icon_url' => 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg',
            ],
            (object) [
                'id' => 2,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
                'icon_url' => 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg',
            ],
            (object) [
                'id' => 3,
                'screen_name' => 'bar',
                'service' => 'mastodon',
                'url' => 'http://bar.example.com',
                'icon_url' => null,
            ],
            (object) [
                'id' => 4,
                'screen_name' => 'baz',
                'service' => 'mastodon',
                'url' => 'https://baz.example.com',
                'icon_url' => 'https://files.mastodon.social/accounts/avatars/000/000/001/original/114514.png',
            ],
        ];

        $this->usersFoo = [
            (object) [
                'id' => 1,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
                'icon_url' => 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg',
            ],
            (object) [
                'id' => 2,
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
                'icon_url' => 'https://pbs.twimg.com/profile_images/114514/example_bigger.jpg',
            ],
        ];
    }

    public function testCount(): void
    {
        /** @var Builder&MockInterface $builder */
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

        /** @var Builder&MockInterface $builder */
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
        /** @var Builder&MockInterface $builder */
        $builder = m::mock(Builder::class);
        $builder->shouldReceive('get->all')
                ->andReturn($this->users);

        /** @var Builder&MockInterface $join_builder */
        $join_builder = m::mock(Builder::class);
        $join_builder->shouldReceive('leftJoin->select')
                ->andReturn($builder);

        DB::shouldReceive('table')
          ->once()
          ->with('users')
          ->andReturn($join_builder);

        $homo = new HomoRepository();
        $actual = $homo->findAll();

        $this->assertEquals($this->users, $actual);
    }

    public function testFindByScreenName(): void
    {
        $screen_name = 'foo';

        /** @var Builder&MockInterface $builder */
        $builder = m::mock(Builder::class);
        $builder->shouldReceive('where->get->all')
                ->andReturn($this->usersFoo);

        /** @var Builder&MockInterface $join_builder */
        $join_builder = m::mock(Builder::class);
        $join_builder->shouldReceive('leftJoin->select')
                ->andReturn($builder);

        DB::shouldReceive('table')
          ->once()
          ->with('users')
          ->andReturn($join_builder);

        $homo = new HomoRepository();
        $actual = $homo->findByScreenName($screen_name);

        $this->assertEquals($this->usersFoo, $actual);
    }

    public function testExport(): void
    {
        /** @var Builder&MockInterface $builder */
        $builder = m::mock(Builder::class);
        $builder->from = 'users';
        $builder->shouldReceive('get')
                ->andReturn(collect([
                    (object) [
                        'screen_name' => 'foo',
                        'service' => 'twitter',
                        'url' => 'https://foo.example.com/1',
                    ],
                    (object) [
                        'screen_name' => 'foo',
                        'service' => 'twitter',
                        'url' => 'https://foo.example.com/2',
                    ],
                    (object) [
                        'screen_name' => 'bar',
                        'service' => 'mastodon',
                        'url' => 'http://bar.example.com',
                    ],
                    (object) [
                        'screen_name' => 'baz',
                        'service' => 'mastodon',
                        'url' => 'https://baz.example.com',
                    ],
                ]));

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
