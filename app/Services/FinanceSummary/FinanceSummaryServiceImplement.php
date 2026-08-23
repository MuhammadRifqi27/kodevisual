<?php

namespace App\Services\FinanceSummary;

use App\Models\FinanceNetWorthSnapshot;
use App\Repositories\FinanceNetWorthSnapshot\FinanceNetWorthSnapshotRepository;
use App\Repositories\FinancePortfolio\FinancePortfolioRepository;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\FinanceCycle\FinanceCycleService;
use App\Services\FinancialAdvice\FinancialAdviceService;
use Carbon\Carbon;

class FinanceSummaryServiceImplement implements FinanceSummaryService
{
    public function __construct(
        private FinancePortfolioRepository $financePortfolioRepository,
        private FinanceTransactionRepository $financeTransactionRepository,
        private FinanceNetWorthSnapshotRepository $financeNetWorthSnapshotRepository,
        private FinanceCycleService $financeCycleService,
        private FinancialAdviceService $financialAdviceService,
    ) {
    }

    public function overview(int $userId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $cycle = $this->financeCycleService->forUser($userId, $month, $year);

        $portfolios = $this->financePortfolioRepository->listWithInvestment($userId);
        $totalNetWorth = $portfolios->sum('balance');

        $assetAllocation = $portfolios->map(fn ($p) => [
            'name' => $p->account_name . ' (' . ($p->investment->name ?? 'Other') . ')',
            'value' => (float) $p->balance,
        ])->values();

        $netWorthHistory = $this->financeNetWorthSnapshotRepository->latestForUser($userId, 12)
            ->map(fn ($s) => ['date' => $s->date, 'amount' => (float) $s->amount])
            ->values();

        $categorySummary = $this->financeTransactionRepository->expenseByCategoryNameBetween($userId, $cycle['start'], $cycle['end']);

        $totalIncome = $this->financeTransactionRepository->sumByTypeBetween($userId, 'income', $cycle['start'], $cycle['end']);
        $totalExpense = $this->financeTransactionRepository->sumByTypeBetween($userId, 'expense', $cycle['start'], $cycle['end']);
        $netProfit = $totalIncome - $totalExpense;

        // Monthly trend chart, back to a hardcoded floor of Jan 2026 (pre-existing,
        // will need revisiting after that date — out of scope for this refactor).
        $chartData = [];
        $limitDate = Carbon::create(2026, 1, 1)->startOfDay();
        $currentLoopDate = (clone $startDate);

        while ($currentLoopDate >= $limitDate) {
            $loopCycle = $this->financeCycleService->forUser($userId, $currentLoopDate->month, $currentLoopDate->year);
            $stats = $this->financeTransactionRepository->incomeExpenseBetween($userId, $loopCycle['start'], $loopCycle['end']);

            $chartData[] = [
                'date' => $loopCycle['end']->format('Y-m-d'),
                'label' => $loopCycle['end']->format('M Y'),
                'income' => $stats['income'],
                'expense' => $stats['expense'],
            ];

            $currentLoopDate->subMonth();
            if (count($chartData) >= 24) {
                break;
            }
        }
        $chartData = collect(array_reverse($chartData));

        // The cycle for calendar month (month - 1) always lines up exactly with
        // "the previous cycle" here, in both the <=15 and >15/'last' branches of
        // the payroll-day calculation — verified against the original inline logic.
        $lastMonth = $month - 1;
        $lastYear = $year;
        if ($lastMonth < 1) {
            $lastMonth = 12;
            $lastYear--;
        }
        $lastCycle = $this->financeCycleService->forUser($userId, $lastMonth, $lastYear);
        $lastMonthExpense = $this->financeTransactionRepository->sumByTypeBetween($userId, 'expense', $lastCycle['start'], $lastCycle['end']);

        $advice = $this->financialAdviceService->generate((float) $totalExpense, (float) $lastMonthExpense, $categorySummary, (float) $netProfit);

        return [
            'month' => $month,
            'year' => $year,
            'totalNetWorth' => (float) $totalNetWorth,
            'assetAllocation' => $assetAllocation,
            'netWorthHistory' => $netWorthHistory,
            'categorySummary' => $categorySummary,
            'totalIncome' => (float) $totalIncome,
            'totalExpense' => (float) $totalExpense,
            'netProfit' => (float) $netProfit,
            'chartData' => $chartData,
            'advice' => $advice,
        ];
    }

    public function takeNetWorthSnapshot(int $userId): FinanceNetWorthSnapshot
    {
        $totalBalance = $this->financePortfolioRepository->listWithInvestment($userId)->sum('balance');

        return $this->financeNetWorthSnapshotRepository->upsertForToday($userId, date('Y-m-d'), $totalBalance);
    }
}
