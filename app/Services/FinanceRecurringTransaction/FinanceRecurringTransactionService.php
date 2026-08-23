<?php

namespace App\Services\FinanceRecurringTransaction;

use App\Models\FinanceRecurringTransaction;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\BaseService;

interface FinanceRecurringTransactionService extends BaseService
{
    public function listQuery(int $userId): Builder;

    public function createRecurring(int $userId, array $data): FinanceRecurringTransaction;

    public function deleteRecurring(int $userId, int $id): void;

    /**
     * Generate a real FinanceTransaction for every due template (next_date <= today,
     * is_active) and advance next_date. Always stores `amount` positive with `type`
     * carrying the sign — matching FinancePortfolio::getBalanceAttribute() and every
     * manual-entry path (see docs/money-management-refactor.md bug fix #1).
     * @return int Number of templates processed.
     */
    public function processDue(int $userId): int;
}
