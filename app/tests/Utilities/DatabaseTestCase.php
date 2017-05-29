<?php
namespace HomoChecker\Test\Utilities;

use PHPUnit\DbUnit\TestCase as TestCase;

abstract class DatabaseTestCase extends TestCase
{
    static protected $pdo = null;

    public function getConnection()
    {
        static::$pdo = static::$pdo ?? new \PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASS, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        return $this->connection = $this->connection ?? $this->createDefaultDBConnection(static::$pdo, DB_NAME);
    }
}
