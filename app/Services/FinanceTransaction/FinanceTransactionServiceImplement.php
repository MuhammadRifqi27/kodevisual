<?php

namespace App\Services\FinanceTransaction;

use App\Models\FinanceTransaction;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\FinancePortfolio\FinancePortfolioService;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Service;

class FinanceTransactionServiceImplement extends Service implements FinanceTransactionService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(
        FinanceTransactionRepository $mainRepository,
        private FinancePortfolioService $financePortfolioService,
    ) {
        $this->mainRepository = $mainRepository;
    }

    public function filteredQuery(int $userId, array $filters = []): Builder
    {
        return $this->mainRepository->filteredQuery($userId, $filters);
    }

    public function summaryFor(Builder $query): array
    {
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        return [
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'net_balance' => (float) ($totalIncome - $totalExpense),
        ];
    }

    public function createTransaction(int $userId, array $data): FinanceTransaction
    {
        if (!empty($data['investment_id'])) {
            $this->financePortfolioService->assertOwned($userId, $data['investment_id']);
        }

        return $this->mainRepository->create([
            'user_id' => $userId,
            'date' => $data['date'],
            'type' => $data['type'],
            'finance_category_id' => $data['category_id'],
            'finance_investment_id' => $data['investment_id'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function updateTransaction(int $userId, int $id, array $data): FinanceTransaction
    {
        $this->mainRepository->ownedOrFail($userId, $id);

        if (!empty($data['investment_id'])) {
            $this->financePortfolioService->assertOwned($userId, $data['investment_id']);
        }

        $this->mainRepository->update($id, [
            'date' => $data['date'],
            'type' => $data['type'],
            'finance_category_id' => $data['category_id'],
            'finance_investment_id' => $data['investment_id'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        return $this->mainRepository->find($id);
    }

    public function deleteTransaction(int $userId, int $id): void
    {
        $this->mainRepository->ownedOrFail($userId, $id);
        $this->mainRepository->delete($id);
    }
}
