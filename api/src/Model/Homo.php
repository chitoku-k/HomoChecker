<?php
declare(strict_types=1);

namespace HomoChecker\Model;

class Homo implements HomoInterface
{
    protected $database;
    protected $table;

    public $screen_name;
    public $url;

    public function __construct(\PDO $database = null, string $table = null)
    {
        $this->database = $database;
        $this->table = str_replace(["\0", '`'], ['', '``'], $table);
    }

    /**
     * Fetch all the elements filtered by conditions.
     * @param  string[] $where
     * @return Homo[]
     */
    public function find($where = []): array
    {
        if (!isset($this->database, $this->table)) {
            throw new \RuntimeException('No database or table is specified.');
        }

        $sql[] = "SELECT * FROM `{$this->table}`";

        foreach ($where as $field => $value) {
            $conditions[] = "{$field} = ?";
            $values[] = $value;
        }

        if (isset($conditions)) {
            $sql[] = 'WHERE ' . implode(' AND ', $conditions);
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
