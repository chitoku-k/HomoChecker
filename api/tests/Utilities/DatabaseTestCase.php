<?php
declare(strict_types=1);

namespace HomoChecker\Test\Utilities;

use PHPUnit\DbUnit\TestCase as TestCase;

abstract class DatabaseTestCase extends TestCase
{
    static protected $pdo = null;

    public function getConnection()
    {
        static::$pdo = static::$pdo ?? new \PDO(DB_DSN, DB_USER, DB_PASS, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        if (isset($this->connection)) {
            return $this->connection;
        }
        $name = static::$pdo->query('SELECT DATABASE()')->fetchColumn();
        return $this->connection = $this->createDefaultDBConnection(static::$pdo, $name);
    }
}
