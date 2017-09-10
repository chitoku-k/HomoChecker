<?php
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
        $this->table = str_replace(["\0", "`"], ["", "``"], $table);
        $this->initialize();
    }

    protected function initialize()
    {
        return $this->database->exec("
            CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `screen_name` varchar(20) NOT NULL,
                `url` varchar(255) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `screen_name` (`screen_name`)
            )
        ");
    }

    public function find(array $where = []): array
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
