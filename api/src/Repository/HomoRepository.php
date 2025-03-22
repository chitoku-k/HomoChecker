<?php
declare(strict_types=1);

namespace HomoChecker\Repository;

use HomoChecker\Contracts\Repository\HomoRepository as HomoRepositoryContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

final class HomoRepository implements HomoRepositoryContract
{
    #[\Override]
    public function count(): int
    {
        return DB::table('users')->count();
    }

    #[\Override]
    public function countByScreenName(string $screenName): int
    {
        return DB::table('users')->where('screen_name', $screenName)->count();
    }

    private function join(): Builder
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

    #[\Override]
    public function findAll(): array
    {
        return $this->join()->get()->all();
    }

    #[\Override]
    public function findByScreenName(string $screenName): array
    {
        return $this->join()->where('users.screen_name', $screenName)->get()->all();
    }

    #[\Override]
    public function export(): string
    {
        $builder = DB::table('users');

        // Create a Grammar instance that doesn't parameterize its values
        $grammar = new class(DB::connection()) extends PostgresGrammar {
            /**
             * {@inheritdoc}
             */
            #[\Override]
            public function parameter($value)
            {
                return $this->quoteString($value);
            }
        };

        return $builder
            ->get(['screen_name', 'service', 'url'])
            ->map(fn (\stdClass $item) => $grammar->compileInsert($builder, (array) $item) . ";\n")
            ->join('');
    }
}
