<?php
namespace HomoChecker\Test\Utilities;

use PHPUnit\DbUnit\DataSet\AbstractDataSet as AbstractDataSet;
use PHPUnit\DbUnit\DataSet\DefaultTableMetadata as DefaultTableMetadata;
use PHPUnit\DbUnit\DataSet\DefaultTable as DefaultTable;
use PHPUnit\DbUnit\DataSet\DefaultTableIterator as DefaultTableIterator;

class ArrayDataSet extends AbstractDataSet
{
    protected $tables = [];

    public function __construct(array $data)
    {
        foreach ($data as $name => $rows) {
            $columns = array_keys($rows[0] ?? []);
            $metaData = new DefaultTableMetadata($name, $columns);
            $table = new DefaultTable($metaData);

            foreach ($rows as $row) {
                $table->addRow($row);
            }

            $this->tables[$name] = $table;
        }
    }

    protected function createIterator($reverse = false)
    {
        return new DefaultTableIterator($this->tables, $reverse);
    }

    public function getTable($name)
    {
        if (!isset($this->tables[$name])) {
            throw new \RuntimeException("No table found: \"$name\"");
        }

        return $this->tables[$name];
    }
}
