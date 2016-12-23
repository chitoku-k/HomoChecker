<?php
namespace HomoChecker\Model;

use Interop\Container\ContainerInterface as Container;

class Homo
{
    protected $pdo;
    protected $container;
    protected $table;

    public $screen_name;
    public $url;

    public function __construct(Container $container, string $table = 'users')
    {
        $this->container = $container;
        $this->table = $table;
    }

    public function find(array $where = [])
    {
        $sql[] = "SELECT * FROM `{$this->table}`";

        foreach ($where as $field => $value) {
            $conditions[] = "{$field} = ?";
            $values[] = $value;
        }

        if (isset($conditions)) {
            $sql[] = "WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->container->database->prepare(implode(' ', $sql));
        foreach ($values ?? [] as $index => $value) {
            $stmt->bindValue($index, $value, \PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            static::class,
            [
                $this->container,
                $this->table,
            ]
        );
    }
}
