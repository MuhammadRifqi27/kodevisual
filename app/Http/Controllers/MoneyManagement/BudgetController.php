<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceBudget;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\FinanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        // Fetch payroll start day from settings (default to 25)
        $payrollDay = FinanceSetting::where('user_id', $userId)->where('key', 'payroll_start_day')->first()?->value ?? 25;
        
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

        $categories = FinanceCategory::where('type', 'expense')->orderBy('name')->get();
        
        $budgets = FinanceBudget::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('finance_category_id');

        // Calculate actual spending for each category using the cycle period
        $spending = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->select('finance_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('finance_category_id')
            ->get()
            ->keyBy('finance_category_id');

        // Calculate total income for this cycle
        $incomePool = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->sum('amount');

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $spending->sum('total');

        return view('pages.money-management.budgets.index', compact(
            'categories', 
            'budgets', 
            'spending', 
            'month', 
            'year', 
            'cycleStartDate', 
            'cycleEndDate',
            'incomePool',
            'totalBudget',
            'totalSpent'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'finance_category_id' => 'required|exists:finance_categories,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        FinanceBudget::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'finance_category_id' => $request->finance_category_id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            ['amount' => $request->amount]
        );

        return response()->json(['success' => 'Budget berhasil disimpan']);
    }
}
