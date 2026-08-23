<?php

namespace App\Repositories\FinanceBudget;

use App\Models\FinanceBudget;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceBudgetRepository extends Repository
{
    /**
     * @return Collection Keyed by finance_category_id.
     */
    public function forUserMonthYear(int $userId, int $month, int $year): Collection;

    public function upsert(int $userId, int $categoryId, int $month, int $year, float $amount): FinanceBudget;
}
