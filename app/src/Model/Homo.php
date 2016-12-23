<?php
namespace HomoChecker\Model;

class Homo implements HomoInterface
{
    protected $pdo;
    protected $container;
    protected $table;

    public $screen_name;
    public $url;

    public function __construct(\PDO $database, string $table)
    {
        $this->database = $database;
        $this->table = $table;
    }

    public function find(array $where = []): array
    {
        $sql[] = "SELECT * FROM `{$this->table}`";

        foreach ($where as $field => $value) {
            $conditions[] = "{$field} = ?";
            $values[] = $value;
        }

        if (isset($conditions)) {
            $sql[] = "WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->database->prepare(implode(' ', $sql));
        foreach ($values ?? [] as $index => $value) {
            $stmt->bindValue($index + 1, $value, \PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            static::class,
            [
                $this->database,
                $this->table,
            ]
        );
    }
}
