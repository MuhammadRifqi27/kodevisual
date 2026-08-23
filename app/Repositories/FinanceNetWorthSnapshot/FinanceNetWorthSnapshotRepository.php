<?php

namespace App\Repositories\FinanceNetWorthSnapshot;

use App\Models\FinanceNetWorthSnapshot;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceNetWorthSnapshotRepository extends Repository
{
    /**
     * Latest N snapshots for a user, sorted ascending (oldest first) for charting.
     */
    public function latestForUser(int $userId, int $limit = 12): Collection;

    public function upsertForToday(int $userId, string $date, float $amount): FinanceNetWorthSnapshot;
}
