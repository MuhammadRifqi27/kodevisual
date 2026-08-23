<?php

namespace App\Services\FinanceBudget;

use App\Models\FinanceBudget;
use App\Repositories\FinanceBudget\FinanceBudgetRepository;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\FinanceCategory\FinanceCategoryService;
use App\Services\FinanceCycle\FinanceCycleService;
use LaravelEasyRepository\Service;

class FinanceBudgetServiceImplement extends Service implements FinanceBudgetService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(
        FinanceBudgetRepository $mainRepository,
        private FinanceCategoryService $financeCategoryService,
        private FinanceTransactionRepository $financeTransactionRepository,
        private FinanceCycleService $financeCycleService,
    ) {
        $this->mainRepository = $mainRepository;
    }

    public function overview(int $userId, int $month, int $year): array
    {
        $cycle = $this->financeCycleService->forUser($userId, $month, $year);

        $categories = $this->financeCategoryService->query('expense')->get();
        $budgets = $this->mainRepository->forUserMonthYear($userId, $month, $year);
        $spending = $this->financeTransactionRepository->groupedByCategoryBetween($userId, 'expense', $cycle['start'], $cycle['end']);
        $incomePool = $this->financeTransactionRepository->sumByTypeBetween($userId, 'income', $cycle['start'], $cycle['end']);

        $categoryBreakdown = $categories->map(function ($category) use ($budgets, $spending) {
            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'budget' => (float) ($budgets[$category->id]->amount ?? 0),
                'spent' => (float) ($spending[$category->id]->total ?? 0),
            ];
        })->values();

        return [
            'categories' => $categories,
            'budgets' => $budgets,
            'spending' => $spending,
            'month' => $month,
            'year' => $year,
            'cycleStartDate' => $cycle['start'],
            'cycleEndDate' => $cycle['end'],
            'incomePool' => (float) $incomePool,
            'totalBudget' => (float) $budgets->sum('amount'),
            'totalSpent' => (float) $spending->sum('total'),
            'categoryBreakdown' => $categoryBreakdown,
        ];
    }

    public function upsert(int $userId, int $categoryId, int $month, int $year, float $amount): FinanceBudget
    {
        return $this->mainRepository->upsert($userId, $categoryId, $month, $year, $amount);
    }
}
