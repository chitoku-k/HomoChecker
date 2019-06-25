<?php
declare(strict_types=1);

namespace HomoChecker\Repository;

use HomoChecker\Contracts\Repository\HomoRepository as HomoRepositoryContract;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Facades\DB;

class HomoRepository implements HomoRepositoryContract
{
    protected $table = 'users';

    public function count(): int
    {
        return DB::table($this->table)->count();
    }

    public function countByScreenName(string $screenName): int
    {
        return DB::table($this->table)->where('screen_name', $screenName)->count();
    }

    public function findAll(): array
    {
        return DB::table($this->table)->get()->all();
    }

    public function findByScreenName(string $screenName): array
    {
        return DB::table($this->table)->where('screen_name', $screenName)->get()->all();
    }

    public function export(): string
    {
        $builder = DB::table($this->table);

        // Create a Grammar instance that doesn't parameterize its values
        $grammar = new class() extends Grammar {
            /**
             * Only quote the given parameter.
             * @return string
             */
            public function parameter($value)
            {
                return $this->quoteString($value);
            }
        };

        return $builder->get()->map(function (\stdClass $item) use ($builder, $grammar) {
            unset($item->id);
            return $grammar->compileInsert($builder, (array) $item) . ';';
        })->join("\n");
    }
}
