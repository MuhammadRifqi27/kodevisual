<?php

namespace App\Repositories\FinanceSetting;

use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceSettingRepository extends Repository
{
    public function allForUser(int $userId): Collection;

    public function get(int $userId, string $key, $default = null);

    public function upsertMany(int $userId, array $keyValues): void;
}
