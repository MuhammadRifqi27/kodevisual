<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinancePortfolio;
use App\Models\FinanceSetting;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $payrollDay = FinanceSetting::where('user_id', $userId)->where('key', 'payroll_start_day')->first()?->value ?? 25;

        $prevMonthDate = (clone $startDate)->subMonth();
        $nextMonthDate = (clone $startDate)->addMonth();

        $calculateStartForBase = function ($base) use ($payrollDay) {
            if ($payrollDay === 'last') {
                return (clone $base)->endOfMonth()->startOfDay();
            }

            $dayToUse = min((int) $payrollDay, $base->daysInMonth);
            return (clone $base)->day($dayToUse)->startOfDay();
        };

        if ($payrollDay === 'last' || (int) $payrollDay > 15) {
            $cycleStartDate = $calculateStartForBase($prevMonthDate);
            $nextCycleStartDate = $calculateStartForBase($startDate);
        } else {
            $cycleStartDate = $calculateStartForBase($startDate);
            $nextCycleStartDate = $calculateStartForBase($nextMonthDate);
        }
        $cycleEndDate = (clone $nextCycleStartDate)->subSecond();

        $portfolios = FinancePortfolio::where('user_id', $userId)->with('investment')->get();
        $portfolioData = [];
        $totalNetWorthAtEnd = 0;
        $totalLiquidCash = 0;
        $totalInvestmentValue = 0;
        $liquidAccounts = [];

        foreach ($portfolios as $portfolio) {
            $prevInvIn = FinanceInvestmentTransaction::where('finance_investment_id', $portfolio->id)->where('user_id', $userId)->where('date', '<=', $cycleEndDate)->whereIn('type', ['deposit', 'profit'])->sum('amount');
            $prevInvOut = FinanceInvestmentTransaction::where('finance_investment_id', $portfolio->id)->where('user_id', $userId)->where('date', '<=', $cycleEndDate)->whereIn('type', ['withdrawal', 'loss'])->sum('amount');

            $genBalance = FinanceTransaction::where('finance_investment_id', $portfolio->id)
                ->where('user_id', $userId)
                ->where('date', '<=', $cycleEndDate)
                ->select(DB::raw("SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as total"))
                ->value('total') ?? 0;

            $balance = ($prevInvIn - $prevInvOut) + $genBalance;
            $totalNetWorthAtEnd += $balance;

            $invId = $portfolio->investment->id ?? null;
            $isInvestment = in_array($invId, [1, 2, 8, 9]);

            if ($isInvestment) {
                $totalInvestmentValue += $balance;
            } else {
                $totalLiquidCash += $balance;
                if ($balance != 0) {
                    $liquidAccounts[] = ['name' => $portfolio->account_name, 'balance' => (float) $balance];
                }
            }

            if ($balance != 0) {
                $portfolioData[] = [
                    'portfolio_id' => $portfolio->id,
                    'name' => $portfolio->account_name,
                    'investment' => $portfolio->investment->name ?? null,
                    'investment_code' => $portfolio->investment->code ?? null,
                    'balance' => (float) $balance,
                    'is_investment' => $isInvestment,
                ];
            }
        }

        $untrackedCash = FinanceTransaction::where('user_id', $userId)
            ->whereNull('finance_investment_id')
            ->where('date', '<=', $cycleEndDate)
            ->select(DB::raw("SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as total"))
            ->value('total') ?? 0;

        $totalLiquidCash += $untrackedCash;
        $totalNetWorthAtEnd += $untrackedCash;

        if ($untrackedCash != 0) {
            $liquidAccounts[] = ['name' => 'Untracked Cash', 'balance' => (float) $untrackedCash];
        }

        $incomePool = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->sum('amount');

        $monthlyExpense = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->sum('amount');

        $incomeBreakdown = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->with('category')
            ->get()
            ->groupBy('finance_category_id')
            ->map(fn ($group) => ['name' => $group->first()->category->name ?? 'Income', 'total' => (float) $group->sum('amount')])
            ->sortByDesc('total')
            ->take(3)
            ->values();

        $topExpenses = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->with('category')
            ->get()
            ->groupBy('finance_category_id')
            ->map(fn ($group) => ['name' => $group->first()->category->name ?? 'Unknown', 'total' => (float) $group->sum('amount')])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $recentTransactions = FinanceTransaction::where('user_id', $userId)
            ->whereIn('type', ['income', 'expense', 'transfer'])
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->with(['category', 'portfolio'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return response()->json([
            'month' => (int) $month,
            'year' => (int) $year,
            'cycle_start_date' => $cycleStartDate->toDateString(),
            'cycle_end_date' => $cycleEndDate->toDateString(),
            'total_net_worth' => (float) $totalNetWorthAtEnd,
            'total_liquid_cash' => (float) $totalLiquidCash,
            'total_investment_value' => (float) $totalInvestmentValue,
            'liquid_accounts' => $liquidAccounts,
            'income_pool' => (float) $incomePool,
            'income_breakdown' => $incomeBreakdown,
            'monthly_expense' => (float) $monthlyExpense,
            'net_profit' => (float) ($incomePool - $monthlyExpense),
            'top_expenses' => $topExpenses,
            'portfolios' => $portfolioData,
            'recent_transactions' => $recentTransactions,
        ]);
    }
}
