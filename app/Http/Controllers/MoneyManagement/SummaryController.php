<?php

namespace App\Http\Controllers\MoneyManagement;

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
        addVendor('apex-chart');

        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $overview = $this->financeSummaryService->overview(auth()->id(), $month, $year);

        return view('pages.money-management.summary.index', [
            'categorySummary' => $overview['categorySummary'],
            'totalIncome' => $overview['totalIncome'],
            'totalExpense' => $overview['totalExpense'],
            'netProfit' => $overview['netProfit'],
            'advice' => $overview['advice'],
            'month' => $overview['month'],
            'year' => $overview['year'],
            'totalNetWorth' => $overview['totalNetWorth'],
            'assetAllocation' => $overview['assetAllocation'],
            'netWorthHistory' => $overview['netWorthHistory'],
            'chartData' => $overview['chartData'],
        ]);
    }

    public function takeNetWorthSnapshot()
    {
        $this->financeSummaryService->takeNetWorthSnapshot(auth()->id());

        return response()->json(['success' => 'Snapshot Net Worth berhasil diambil']);
    }
}
