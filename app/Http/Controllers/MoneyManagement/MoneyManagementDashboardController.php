<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestment;
use App\Models\FinanceSetting;
use App\Models\FinanceTransaction;
use App\Models\FinanceInvestmentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MoneyManagementDashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        // Fetch payroll start day from settings (default to 25)
        $payrollDay = FinanceSetting::where('user_id', $userId)->where('key', 'payroll_start_day')->first()?->value ?? 25;

        // Calculate cycle dates based on the payroll day
        $prevMonthDate = (clone $startDate)->subMonth();
        $nextMonthDate = (clone $startDate)->addMonth();

        $calculateStartForBase = function($base) use ($payrollDay) {
            if ($payrollDay === 'last') {
                return (clone $base)->endOfMonth()->startOfDay();
            } else {
                $dayToUse = min((int)$payrollDay, $base->daysInMonth);
                return (clone $base)->day($dayToUse)->startOfDay();
            }
        };

        if ($payrollDay === 'last' || (int)$payrollDay > 15) {
            $cycleStartDate = $calculateStartForBase($prevMonthDate);
            $nextCycleStartDate = $calculateStartForBase($startDate);
        } else {
            $cycleStartDate = $calculateStartForBase($startDate);
            $nextCycleStartDate = $calculateStartForBase($nextMonthDate);
        }
        $cycleEndDate = (clone $nextCycleStartDate)->subSecond();

        // 1. Calculate Liquid Cash vs Long-term Investments
        $portfolios = \App\Models\FinancePortfolio::where('user_id', $userId)->with('investment')->get();
        $portfolioData = [];
        $totalNetWorthAtEnd = 0;
        $totalLiquidCash = 0;
        $totalInvestmentValue = 0;

        $liquidAccounts = [];
        foreach ($portfolios as $portfolio) {
            $prevInvIn = FinanceInvestmentTransaction::where('finance_investment_id', $portfolio->id)->where('user_id', $userId)->where('date', '<=', $cycleEndDate)->whereIn('type', ['deposit', 'profit'])->sum('amount');
            $prevInvOut = FinanceInvestmentTransaction::where('finance_investment_id', $portfolio->id)->where('user_id', $userId)->where('date', '<=', $cycleEndDate)->whereIn('type', ['withdrawal', 'loss'])->sum('amount');

            // Calculate General Transactions (Income is +, Expense is -, Transfer is signed)
            $genBalance = FinanceTransaction::where('finance_investment_id', $portfolio->id)
                ->where('user_id', $userId)
                ->where('date', '<=', $cycleEndDate)
                ->select(DB::raw("SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as total"))
                ->value('total') ?? 0;

            $balance = ($prevInvIn - $prevInvOut) + $genBalance;
            $totalNetWorthAtEnd += $balance;

            // Heuristic to separate liquid cash from investments
            $invId = strtoupper($portfolio->investment->id ?? '');
            $isInvestment = in_array($invId, [1, 2, 8, 9]);

            if ($isInvestment) {
                $totalInvestmentValue += $balance;
            } else {
                $totalLiquidCash += $balance;
                if ($balance != 0) {
                    $liquidAccounts[] = [
                        'name' => $portfolio->account_name,
                        'balance' => $balance
                    ];
                }
            }

            if ($balance != 0) {
                $portfolioData[] = [
                    'name' => $portfolio->account_name,
                    'investment' => $portfolio->investment->name,
                    'investment-code' => $portfolio->investment->code,
                    'balance' => $balance,
                    'is_investment' => $isInvestment,
                    'assets' => $portfolio->id
                ];
            }
        }

        // Transactions NOT linked to any portfolio are considered liquid cash (Uncategorized Cash)
        $untrackedCash = FinanceTransaction::where('user_id', $userId)
            ->whereNull('finance_investment_id')
            ->where('date', '<=', $cycleEndDate)
            ->select(DB::raw("SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as total"))
            ->value('total') ?? 0;

        $totalLiquidCash += $untrackedCash;
        $totalNetWorthAtEnd += $untrackedCash;

        if ($untrackedCash != 0) {
            $liquidAccounts[] = [
                'name' => 'Untracked Cash',
                'balance' => $untrackedCash
            ];
        }

        // 2. Rolling Cycle Stats
        $incomePool = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->sum('amount');

        $monthlyExpense = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->sum('amount');

        $netProfit = $incomePool - $monthlyExpense;

        // Breakdown of Income Categories for this cycle
        $incomeBreakdown = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->with('category')
            ->get()
            ->groupBy('finance_category_id')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->category->name ?? 'Income',
                    'total' => $group->sum('amount')
                ];
            })
            ->sortByDesc('total')
            ->take(3);

        // 3. Top Spending Categories (This Cycle)
        $topExpenses = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->with('category')
            ->get()
            ->groupBy('finance_category_id')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->category->name ?? 'Unknown',
                    'total' => $group->sum('amount')
                ];
            })
            ->sortByDesc('total')
            ->take(5);

        // 4. Recent Transactions (From cycle start)
        $recentTransactions = FinanceTransaction::where('user_id', $userId)
            ->whereIn('type', ['income', 'expense', 'transfer'])
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->with(['category', 'portfolio'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return view('pages.money-management.dashboard', compact(
            'totalNetWorthAtEnd',
            'totalLiquidCash',
            'totalInvestmentValue',
            'liquidAccounts',
            'incomePool',
            'incomeBreakdown',
            'cycleStartDate',
            'monthlyExpense',
            'netProfit',
            'topExpenses',
            'portfolioData',
            'recentTransactions',
            'month',
            'year'
        ));
    }
}
