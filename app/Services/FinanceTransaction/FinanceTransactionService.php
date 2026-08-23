<?php

namespace App\Services\FinanceTransaction;

use App\Models\FinanceTransaction;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\BaseService;

interface FinanceTransactionService extends BaseService
{
    /**
     * @param array{type?: ?string, category_id?: ?string, start_date?: ?string, end_date?: ?string} $filters
     */
    public function filteredQuery(int $userId, array $filters = []): Builder;

    /**
     * @return array{total_income: float, total_expense: float, net_balance: float}
     */
    public function summaryFor(Builder $query): array;

    public function createTransaction(int $userId, array $data): FinanceTransaction;

    public function updateTransaction(int $userId, int $id, array $data): FinanceTransaction;

    public function deleteTransaction(int $userId, int $id): void;
}
