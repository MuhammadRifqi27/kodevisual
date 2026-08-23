<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceDashboard\FinanceDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private FinanceDashboardService $financeDashboardService)
    {
    }

    public function index(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $overview = $this->financeDashboardService->overview(auth()->id(), $month, $year);

        $portfolios = array_map(fn ($p) => [
            'portfolio_id' => $p['portfolio']->id,
            'name' => $p['portfolio']->account_name,
            'investment' => $p['portfolio']->investment->name ?? null,
            'investment_code' => $p['portfolio']->investment->code ?? null,
            'balance' => $p['balance'],
            'is_investment' => $p['isInvestment'],
        ], $overview['portfolioBalances']);

        return response()->json([
            'month' => (int) $overview['month'],
            'year' => (int) $overview['year'],
            'cycle_start_date' => $overview['cycleStartDate']->toDateString(),
            'cycle_end_date' => $overview['cycleEndDate']->toDateString(),
            'total_net_worth' => $overview['totalNetWorthAtEnd'],
            'total_liquid_cash' => $overview['totalLiquidCash'],
            'total_investment_value' => $overview['totalInvestmentValue'],
            'liquid_accounts' => $overview['liquidAccounts'],
            'income_pool' => $overview['incomePool'],
            'income_breakdown' => $overview['incomeBreakdown'],
            'monthly_expense' => $overview['monthlyExpense'],
            'net_profit' => $overview['netProfit'],
            'top_expenses' => $overview['topExpenses'],
            'portfolios' => $portfolios,
            'recent_transactions' => $overview['recentTransactions'],
        ]);
    }
}
