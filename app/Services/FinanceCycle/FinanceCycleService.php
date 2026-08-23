<?php

namespace App\Services\FinanceCycle;

/**
 * Not model-backed, so this deliberately does not extend LaravelEasyRepository\BaseService.
 * Computes the "payroll cycle" reporting window used across Budget/Dashboard/Summary.
 */
interface FinanceCycleService
{
    /**
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon, next_start: \Carbon\Carbon}
     */
    public function forUser(int $userId, int $month, int $year): array;

    /**
     * @param string|int $payrollDay Day-of-month (1-31) or the literal string 'last'.
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon, next_start: \Carbon\Carbon}
     */
    public function forPayrollDay($payrollDay, int $month, int $year): array;
}
