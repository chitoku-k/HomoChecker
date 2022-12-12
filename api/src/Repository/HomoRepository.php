<?php
declare(strict_types=1);

namespace HomoChecker\Repository;

use HomoChecker\Contracts\Repository\HomoRepository as HomoRepositoryContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class HomoRepository implements HomoRepositoryContract
{
    public function count(): int
    {
        return DB::table('users')->count();
    }

    public function countByScreenName(string $screenName): int
    {
        return DB::table('users')->where('screen_name', $screenName)->count();
    }

    protected function join(): Builder
    {
        return DB::table('users')
            ->leftJoin(
                'profiles',
                fn (JoinClause $join) => $join
                    ->on('users.screen_name', '=', 'profiles.screen_name')
                    ->whereRaw('profiles.expires_at >= CURRENT_TIMESTAMP'),
            )
            ->select([
                'users.id',
                'users.screen_name',
                'users.service',
                'users.url',
                'profiles.icon_url',
            ]);
    }

    public function findAll(): array
    {
        return $this->join()->get()->all();
    }

    public function findByScreenName(string $screenName): array
    {
        return $this->join()->where('screen_name', $screenName)->get()->all();
    }

    public function export(): string
    {
        $builder = DB::table('users');

        // Create a Grammar instance that doesn't parameterize its values
        $grammar = new class() extends PostgresGrammar {
            /**
             * {@inheritdoc}
             */
            public function parameter($value)
            {
                return $this->quoteString($value);
            }
        };

        return $builder
            ->get(['screen_name', 'service', 'url'])
            ->map(fn (\stdClass $item) => $grammar->compileInsert($builder, (array) $item) . ';')
            ->join("\n");
    }
}
