<?php
declare(strict_types=1);

namespace HomoChecker\Repository;

use HomoChecker\Contracts\Repository\AltsvcRepository as AltsvcRepositoryContract;
use Illuminate\Support\Facades\DB;

class AltsvcRepository implements AltsvcRepositoryContract
{
    public function findAll(): array
    {
        return DB::table('altsvcs')->whereRaw('altsvcs.expires_at >= CURRENT_TIMESTAMP')->get()->all();
    }

    public function save(string $url, string $protocol, string $expiresAt): void
    {
        DB::table('altsvcs')
            ->upsert(
                [
                    [
                        'url' => $url,
                        'protocol' => $protocol,
                        'expires_at' => $expiresAt,
                    ],
                ],
                ['url'],
                ['protocol', 'expires_at'],
            );
    }
}
