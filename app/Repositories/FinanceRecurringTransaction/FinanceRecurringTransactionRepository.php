<?php

namespace App\Repositories\FinanceRecurringTransaction;

use App\Models\FinanceRecurringTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceRecurringTransactionRepository extends Repository
{
    public function ownedOrFail(int $userId, $id): FinanceRecurringTransaction;

    public function queryForUser(int $userId): Builder;

    public function duePending(int $userId, Carbon $today): Collection;
}
