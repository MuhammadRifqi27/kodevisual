<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceBudget\FinanceBudgetService;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(private FinanceBudgetService $financeBudgetService)
    {
    }

    /**
     * Budget vs actual spending for a given month/year, computed over the user's
     * payroll cycle (finance_settings.payroll_start_day) rather than the calendar month.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        $overview = $this->financeBudgetService->overview(auth()->id(), $month, $year);

        return response()->json([
            'month' => (int) $overview['month'],
            'year' => (int) $overview['year'],
            'cycle_start_date' => $overview['cycleStartDate']->toDateString(),
            'cycle_end_date' => $overview['cycleEndDate']->toDateString(),
            'income_pool' => $overview['incomePool'],
            'total_budget' => $overview['totalBudget'],
            'total_spent' => $overview['totalSpent'],
            'categories' => $overview['categoryBreakdown'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'finance_category_id' => 'required|exists:finance_categories,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        $budget = $this->financeBudgetService->upsert(
            auth()->id(),
            $validated['finance_category_id'],
            $validated['month'],
            $validated['year'],
            $validated['amount']
        );

        return response()->json($budget);
    }
}
