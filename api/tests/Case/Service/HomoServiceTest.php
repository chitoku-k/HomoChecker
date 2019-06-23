<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service;

use HomoChecker\Contracts\Repository\HomoRepository;
use HomoChecker\Domain\Homo;
use HomoChecker\Service\HomoService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class HomoServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        parent::setUp();

        $this->users = [
            new Homo([
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/1',
            ]),
            new Homo([
                'screen_name' => 'foo',
                'service' => 'twitter',
                'url' => 'https://foo.example.com/2',
            ]),
            new Homo([
                'screen_name' => 'bar',
                'service' => 'mastodon',
                'url' => 'http://bar.example.com',
            ]),
        ];
    }

    public function testCountAll(): void
    {
        $repository = m::mock(HomoRepository::class);
        $repository->shouldReceive('count')
                   ->andReturn(3);

        $homo = new HomoService($repository);

        $this->assertEquals(3, $homo->count());
    }

    public function testCountByScreenName(): void
    {
        $screen_name = 'foo';

        $repository = m::mock(HomoRepository::class);
        $repository->shouldReceive('countByScreenName')
                   ->with($screen_name)
                   ->andReturn(2);

        $homo = new HomoService($repository);

        $this->assertEquals(2, $homo->count('foo'));
    }

    public function testFindAll(): void
    {
        $repository = m::mock(HomoRepository::class);
        $repository->shouldReceive('findAll')
                   ->andReturn($this->users);

        $homo = new HomoService($repository);

        $this->assertEquals($this->users, $homo->find());
    }

    public function testFindByScreenName(): void
    {
        $screen_name = 'foo';

        $repository = m::mock(HomoRepository::class);
        $repository->shouldReceive('findByScreenName')
                   ->with($screen_name)
                   ->andReturn($this->users);

        $homo = new HomoService($repository);

        $this->assertEquals($this->users, $homo->find('foo'));
    }

    public function testExport(): void
    {
        $sql = <<<SQL
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/1');
        insert into "users" ("screen_name", "service", "url") values ('foo', 'twitter', 'https://foo.example.com/2');
        insert into "users" ("screen_name", "service", "url") values ('bar', 'twitter', 'https://bar.example.com');
        SQL;

        $repository = m::mock(HomoRepository::class);
        $repository->shouldReceive('export')
                   ->andReturn($sql);

        $homo = new HomoService($repository);

        $this->assertEquals($sql, $homo->export());
    }
}
