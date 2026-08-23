<?php

namespace App\Services\FinanceBudget;

use App\Models\FinanceBudget;
use LaravelEasyRepository\BaseService;

interface FinanceBudgetService extends BaseService
{
    /**
     * Budget vs actual spending for one payroll cycle. Keys match what the
     * web Blade view expects via compact() — see MoneyManagement\BudgetController.
     */
    public function overview(int $userId, int $month, int $year): array;

    public function upsert(int $userId, int $categoryId, int $month, int $year, float $amount): FinanceBudget;
}
