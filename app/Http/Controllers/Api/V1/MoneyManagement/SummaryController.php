<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceSummary\FinanceSummaryService;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function __construct(private FinanceSummaryService $financeSummaryService)
    {
    }

    public function index(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $overview = $this->financeSummaryService->overview(auth()->id(), $month, $year);

        return response()->json([
            'month' => (int) $overview['month'],
            'year' => (int) $overview['year'],
            'total_net_worth' => $overview['totalNetWorth'],
            'asset_allocation' => $overview['assetAllocation'],
            'net_worth_history' => $overview['netWorthHistory'],
            'category_summary' => $overview['categorySummary'],
            'total_income' => $overview['totalIncome'],
            'total_expense' => $overview['totalExpense'],
            'net_profit' => $overview['netProfit'],
            'chart_data' => $overview['chartData'],
            'advice' => $overview['advice'],
        ]);
    }

    public function takeNetWorthSnapshot()
    {
        $snapshot = $this->financeSummaryService->takeNetWorthSnapshot(auth()->id());

        return response()->json($snapshot);
    }
}
