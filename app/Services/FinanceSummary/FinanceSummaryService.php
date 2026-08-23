<?php

namespace App\Services\FinanceSummary;

use App\Models\FinanceNetWorthSnapshot;

/**
 * Not model-backed (aggregates several models), so this deliberately does not
 * extend LaravelEasyRepository\BaseService.
 */
interface FinanceSummaryService
{
    public function overview(int $userId, int $month, int $year): array;

    public function takeNetWorthSnapshot(int $userId): FinanceNetWorthSnapshot;
}
