<?php

namespace App\Services\FinanceSetting;

use Illuminate\Support\Collection;
use LaravelEasyRepository\BaseService;

interface FinanceSettingService extends BaseService
{
    public function allForUser(int $userId): Collection;

    public function saveMany(int $userId, array $keyValues): void;
}
