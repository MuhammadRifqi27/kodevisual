<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceBudget;
use App\Models\FinanceCategory;
use App\Models\FinanceSetting;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Budget vs actual spending for a given month/year, computed over the user's
     * payroll cycle (finance_settings.payroll_start_day) rather than the calendar month.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $month = $request->get('month', date('n'));
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

        $categories = FinanceCategory::where('type', 'expense')->orderBy('name')->get();

        $budgets = FinanceBudget::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('finance_category_id');

        $spending = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->select('finance_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('finance_category_id')
            ->get()
            ->keyBy('finance_category_id');

        $incomePool = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->sum('amount');

        $categoryBreakdown = $categories->map(function ($category) use ($budgets, $spending) {
            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'budget' => (float) ($budgets[$category->id]->amount ?? 0),
                'spent' => (float) ($spending[$category->id]->total ?? 0),
            ];
        });

        return response()->json([
            'month' => (int) $month,
            'year' => (int) $year,
            'cycle_start_date' => $cycleStartDate->toDateString(),
            'cycle_end_date' => $cycleEndDate->toDateString(),
            'income_pool' => (float) $incomePool,
            'total_budget' => (float) $budgets->sum('amount'),
            'total_spent' => (float) $spending->sum('total'),
            'categories' => $categoryBreakdown,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'finance_category_id' => 'required|exists:finance_categories,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        $budget = FinanceBudget::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'finance_category_id' => $request->finance_category_id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            ['amount' => $request->amount]
        );

        return response()->json($budget);
    }
}
