<?php
declare(strict_types=1);

namespace HomoChecker\Repository;

use HomoChecker\Contracts\Repository\ProfileRepository as ProfileRepositoryContract;
use Illuminate\Support\Facades\DB;

class ProfileRepository implements ProfileRepositoryContract
{
    public function save(string $screenName, string $iconURL, string $expiresAt): void
    {
        DB::table('profiles')
            ->upsert(
                [
                    [
                        'screen_name' => $screenName,
                        'icon_url' => $iconURL,
                        'expires_at' => $expiresAt,
                    ],
                ],
                ['screen_name'],
                ['icon_url', 'expires_at'],
            );
    }
}
