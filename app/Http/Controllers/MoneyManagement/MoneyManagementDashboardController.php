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
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Fetch payroll start day from settings (default to 25)
        $payrollDay = FinanceSetting::where('user_id', $userId)->where('key', 'payroll_start_day')->first()?->value ?? 25;
        
        // Calculate cycle start date based on the payroll day
        $prevMonth = (clone $startDate)->subMonth();
        if ($payrollDay === 'last') {
            $cycleStartDate = $prevMonth->endOfMonth()->startOfDay();
        } else {
            // Ensure the day doesn't overflow for shorter months (e.g. Day 31 in February)
            // Selecting 'Day 31' is safe; it will automatically use the last day for shorter months.
            $dayToUse = min((int)$payrollDay, $prevMonth->daysInMonth);
            $cycleStartDate = $prevMonth->day($dayToUse)->startOfDay();
        }

        // 1. Calculate Liquid Cash vs Long-term Investments
        $investments = FinanceInvestment::all();
        $portfolioData = [];
        $totalNetWorthAtEnd = 0;
        $totalLiquidCash = 0;
        $totalInvestmentValue = 0;

        $liquidAccounts = [];
        foreach ($investments as $inv) {
            $prevInvIn = FinanceInvestmentTransaction::where('finance_investment_id', $inv->id)->where('user_id', $userId)->where('date', '<=', $endDate)->whereIn('type', ['deposit', 'profit'])->sum('amount');
            $prevInvOut = FinanceInvestmentTransaction::where('finance_investment_id', $inv->id)->where('user_id', $userId)->where('date', '<=', $endDate)->whereIn('type', ['withdrawal', 'loss'])->sum('amount');
            
            // Calculate General Transactions (Income is +, Expense is -, Transfer is signed)
            $genBalance = FinanceTransaction::where('finance_investment_id', $inv->id)
                ->where('user_id', $userId)
                ->where('date', '<=', $endDate)
                ->select(DB::raw("SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as total"))
                ->value('total') ?? 0;
            
            $balance = ($prevInvIn - $prevInvOut) + $genBalance;
            $totalNetWorthAtEnd += $balance;

            // Heuristic to separate liquid cash from investments
            $isInvestment = in_array(strtoupper($inv->name), ['GOLD', 'BITCOIN', 'CRYPTO', 'SAHAM', 'STOCK']) || in_array(strtoupper($inv->code), ['XAU', 'BTC', 'ETH']);
            
            if ($isInvestment) {
                $totalInvestmentValue += $balance;
            } else {
                $totalLiquidCash += $balance;
                if ($balance != 0) {
                    $liquidAccounts[] = [
                        'name' => $inv->name,
                        'balance' => $balance
                    ];
                }
            }

            if ($balance != 0) {
                $portfolioData[] = [
                    'name' => $inv->name,
                    'balance' => $balance,
                    'is_investment' => $isInvestment
                ];
            }
        }

        // Transactions NOT linked to any portfolio are considered liquid cash (Uncategorized Cash)
        $untrackedCash = FinanceTransaction::where('user_id', $userId)
            ->whereNull('finance_investment_id')
            ->where('date', '<=', $endDate)
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

        // 2. Rolling Cycle Stats (from 7 days before month start up to end of month)
        $incomePool = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $endDate])
            ->sum('amount');

        $monthlyExpense = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$cycleStartDate, $endDate])
            ->sum('amount');

        $netProfit = $incomePool - $monthlyExpense;

        // Breakdown of Income Categories for this cycle
        $incomeBreakdown = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $endDate])
            ->with('category')
            ->get()
            ->groupBy('finance_category_id')
            ->map(function($group) {
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
            ->whereBetween('date', [$cycleStartDate, $endDate])
            ->with('category')
            ->get()
            ->groupBy('finance_category_id')
            ->map(function($group) {
                return [
                    'name' => $group->first()->category->name ?? 'Unknown',
                    'total' => $group->sum('amount')
                ];
            })
            ->sortByDesc('total')
            ->take(5);

        // 4. Recent Transactions (From cycle start)
        $recentTransactions = FinanceTransaction::where('user_id', $userId)
            ->whereIn('type', ['income', 'expense'])
            ->whereBetween('date', [$cycleStartDate, $endDate])
            ->with(['category', 'investment'])
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
