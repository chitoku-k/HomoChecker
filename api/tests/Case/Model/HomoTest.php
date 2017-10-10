<?php
declare(strict_types=1);

namespace HomoChecker\Test\Model;

use HomoChecker\Test\Utilities\DatabaseTestCase;
use HomoChecker\Test\Utilities\ArrayDataSet;
use HomoChecker\Model\Homo;

class HomoTest extends DatabaseTestCase
{
    protected static $homo;

    public function getDataSet()
    {
        return new ArrayDataSet([
            'users' => [
                [
                    'id' => 1,
                    'screen_name' => 'foo',
                    'url' => 'https://foo.example.com/1',
                ],
                [
                    'id' => 2,
                    'screen_name' => 'foo',
                    'url' => 'https://foo.example.com/2',
                ],
                [
                    'id' => 3,
                    'screen_name' => 'bar',
                    'url' => 'http://bar.example.com',
                ],
                [
                    'id' => 4,
                    'screen_name' => 'baz',
                    'url' => 'https://baz.example.com',
                ],
            ],
        ]);
    }

    protected function getSetUpOperation()
    {
        $this->connection->getConnection()->query('
            CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `screen_name` varchar(20) NOT NULL,
                `url` varchar(255) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `screen_name` (`screen_name`)
            ) DEFAULT CHARSET=utf8;
        ');
        return parent::getSetUpOperation();
    }

    public function setUp()
    {
        parent::setUp();
        $this->Homo = static::$homo ?? static::$homo = new Homo($this->connection->getConnection(), 'users');
    }

    public function testFindAll(): void
    {
        $actual = $this->Homo->find();

        $this->assertCount(4, $actual);
        $this->assertContainsOnlyInstancesOf(Homo::class, $actual);

        foreach ($actual as $item) {
            $this->assertInternalType('string', $item->id);
            $this->assertInternalType('string', $item->screen_name);
            $this->assertInternalType('string', $item->url);
        }
    }

    public function testFindByScreenName(): void
    {
        $actual = $this->Homo->find([
            'screen_name' => 'foo',
        ]);

        $this->assertCount(2, $actual);
        $this->assertContainsOnlyInstancesOf(Homo::class, $actual);

        foreach ($actual as $item) {
            $this->assertEquals('foo', $item->screen_name);
            $this->assertInternalType('string', $item->url);
        }
    }

    public function testFindByScreenNameAndUrl(): void
    {
        $actual = $this->Homo->find([
            'screen_name' => 'foo',
            'url' => 'https://foo.example.com/1',
        ]);

        $this->assertCount(1, $actual);

        foreach ($actual as $item) {
            $this->assertEquals('foo', $item->screen_name);
            $this->assertEquals('https://foo.example.com/1', $item->url);
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFindThrows(): void
    {
        $homo = new Homo();
        $homo->find();
    }
}
