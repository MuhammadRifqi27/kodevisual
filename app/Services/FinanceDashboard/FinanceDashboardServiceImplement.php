<?php

namespace App\Services\FinanceDashboard;

use App\Repositories\FinanceInvestmentTransaction\FinanceInvestmentTransactionRepository;
use App\Repositories\FinancePortfolio\FinancePortfolioRepository;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\FinanceCycle\FinanceCycleService;

class FinanceDashboardServiceImplement implements FinanceDashboardService
{
    public function __construct(
        private FinancePortfolioRepository $financePortfolioRepository,
        private FinanceInvestmentTransactionRepository $investmentTransactionRepository,
        private FinanceTransactionRepository $financeTransactionRepository,
        private FinanceCycleService $financeCycleService,
    ) {
    }

    public function overview(int $userId, int $month, int $year): array
    {
        $cycle = $this->financeCycleService->forUser($userId, $month, $year);
        $investmentAccountIds = config('finance.investment_account_ids', []);

        $portfolios = $this->financePortfolioRepository->listWithInvestment($userId);

        $portfolioBalances = [];
        $totalNetWorthAtEnd = 0.0;
        $totalLiquidCash = 0.0;
        $totalInvestmentValue = 0.0;
        $liquidAccounts = [];

        foreach ($portfolios as $portfolio) {
            $invBalance = $this->investmentTransactionRepository->signedBalanceAsOf($portfolio->id, $userId, $cycle['end']);
            $genBalance = $this->financeTransactionRepository->signedBalanceAsOf($portfolio->id, $userId, $cycle['end']);
            $balance = $invBalance + $genBalance;
            $totalNetWorthAtEnd += $balance;

            // See config/finance.php — bug fix #2: web and API used to disagree on this list.
            $isInvestment = in_array($portfolio->finance_investment_id, $investmentAccountIds);

            if ($isInvestment) {
                $totalInvestmentValue += $balance;
            } else {
                $totalLiquidCash += $balance;
                if ($balance != 0) {
                    $liquidAccounts[] = ['name' => $portfolio->account_name, 'balance' => (float) $balance];
                }
            }

            if ($balance != 0) {
                $portfolioBalances[] = [
                    'portfolio' => $portfolio,
                    'balance' => (float) $balance,
                    'isInvestment' => $isInvestment,
                ];
            }
        }

        $untrackedCash = $this->financeTransactionRepository->untrackedCashAsOf($userId, $cycle['end']);
        $totalLiquidCash += $untrackedCash;
        $totalNetWorthAtEnd += $untrackedCash;

        if ($untrackedCash != 0) {
            $liquidAccounts[] = ['name' => 'Untracked Cash', 'balance' => (float) $untrackedCash];
        }

        $incomePool = $this->financeTransactionRepository->sumByTypeBetween($userId, 'income', $cycle['start'], $cycle['end']);
        $monthlyExpense = $this->financeTransactionRepository->sumByTypeBetween($userId, 'expense', $cycle['start'], $cycle['end']);

        $incomeBreakdown = $this->financeTransactionRepository->topCategoriesBetween($userId, 'income', $cycle['start'], $cycle['end'], 3, 'Income');
        $topExpenses = $this->financeTransactionRepository->topCategoriesBetween($userId, 'expense', $cycle['start'], $cycle['end'], 5, 'Unknown');
        $recentTransactions = $this->financeTransactionRepository->recentBetween($userId, $cycle['start'], $cycle['end'], 15);

        return [
            'month' => $month,
            'year' => $year,
            'cycleStartDate' => $cycle['start'],
            'cycleEndDate' => $cycle['end'],
            'totalNetWorthAtEnd' => (float) $totalNetWorthAtEnd,
            'totalLiquidCash' => (float) $totalLiquidCash,
            'totalInvestmentValue' => (float) $totalInvestmentValue,
            'liquidAccounts' => $liquidAccounts,
            'portfolioBalances' => $portfolioBalances,
            'incomePool' => (float) $incomePool,
            'monthlyExpense' => (float) $monthlyExpense,
            'netProfit' => (float) ($incomePool - $monthlyExpense),
            'incomeBreakdown' => $incomeBreakdown,
            'topExpenses' => $topExpenses,
            'recentTransactions' => $recentTransactions,
        ];
    }
}
