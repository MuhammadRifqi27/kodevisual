<?php

namespace App\Services\FinanceDashboard;

/**
 * Not model-backed (aggregates several models), so this deliberately does not
 * extend LaravelEasyRepository\BaseService.
 */
interface FinanceDashboardService
{
    /**
     * Net-worth / liquid-vs-investment breakdown for one payroll cycle.
     * Returns raw aggregates (Carbon dates, per-portfolio balance + isInvestment
     * flag) — each caller (web view, API JSON) shapes its own display array
     * from these, since the two currently use different key names/casing.
     */
    public function overview(int $userId, int $month, int $year): array;
}
