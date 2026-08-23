<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceDashboard\FinanceDashboardService;
use Illuminate\Http\Request;

class MoneyManagementDashboardController extends Controller
{
    public function __construct(private FinanceDashboardService $financeDashboardService)
    {
    }

    public function index(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $overview = $this->financeDashboardService->overview(auth()->id(), $month, $year);

        $portfolioData = array_map(fn ($p) => [
            'name' => $p['portfolio']->account_name,
            'investment' => $p['portfolio']->investment->name,
            'investment-code' => $p['portfolio']->investment->code,
            'balance' => $p['balance'],
            'is_investment' => $p['isInvestment'],
            'assets' => $p['portfolio']->id,
        ], $overview['portfolioBalances']);

        return view('pages.money-management.dashboard', [
            'totalNetWorthAtEnd' => $overview['totalNetWorthAtEnd'],
            'totalLiquidCash' => $overview['totalLiquidCash'],
            'totalInvestmentValue' => $overview['totalInvestmentValue'],
            'liquidAccounts' => $overview['liquidAccounts'],
            'incomePool' => $overview['incomePool'],
            'incomeBreakdown' => $overview['incomeBreakdown'],
            'cycleStartDate' => $overview['cycleStartDate'],
            'monthlyExpense' => $overview['monthlyExpense'],
            'netProfit' => $overview['netProfit'],
            'topExpenses' => $overview['topExpenses'],
            'portfolioData' => $portfolioData,
            'recentTransactions' => $overview['recentTransactions'],
            'month' => $overview['month'],
            'year' => $overview['year'],
        ]);
    }
}
