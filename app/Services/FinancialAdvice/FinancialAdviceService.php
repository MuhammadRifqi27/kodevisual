<?php

namespace App\Services\FinancialAdvice;

use Illuminate\Support\Collection;

/**
 * Pure computation, no repository — not model-backed, so this does not
 * extend LaravelEasyRepository\BaseService.
 */
interface FinancialAdviceService
{
    /**
     * @param Collection $categorySummary Rows with ->name and ->total, ordered by total desc.
     * @return string[]
     */
    public function generate(float $currentExpense, float $lastExpense, Collection $categorySummary, float $netProfit): array;
}
