<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestment;
use App\Models\FinanceTransaction;
use App\Models\FinanceInvestmentTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MoneyManagementDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Total Balance Across All Portfolios
        $investments = FinanceInvestment::with([
            'transactions' => function($q) use ($userId) { $q->where('user_id', $userId); },
            'generalTransactions' => function($q) use ($userId) { $q->where('user_id', $userId); }
        ])->get();

        $totalBalance = 0;
        $portfolioData = [];
        foreach ($investments as $inv) {
            $invTrxIn = $inv->transactions->whereIn('type', ['deposit', 'profit'])->sum('amount');
            $invTrxOut = $inv->transactions->whereIn('type', ['withdrawal', 'loss'])->sum('amount');
            $genTrxIn = $inv->generalTransactions->where('type', 'income')->sum('amount');
            $genTrxOut = $inv->generalTransactions->where('type', 'expense')->sum('amount');
            
            $balance = ($invTrxIn + $genTrxIn) - ($invTrxOut + $genTrxOut);
            $totalBalance += $balance;
            
            if ($balance > 0) {
                $portfolioData[] = [
                    'name' => $inv->name,
                    'balance' => $balance
                ];
            }
        }

        // 2. Monthly Stats (P&L)
        $monthlyIncome = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
            
        $monthlyExpense = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $netProfit = $monthlyIncome - $monthlyExpense;

        // 3. Top Spending Categories (This Month)
        $topExpenses = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
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

        // 4. Recent Transactions
        $recentTransactions = FinanceTransaction::where('user_id', $userId)
            ->with(['category', 'investment'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('pages.money-management.dashboard', compact(
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'netProfit',
            'topExpenses',
            'portfolioData',
            'recentTransactions'
        ));
    }
}
