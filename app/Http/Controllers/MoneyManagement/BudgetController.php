<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceBudget;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        $categories = FinanceCategory::where('type', 'expense')->orderBy('name')->get();
        
        $budgets = FinanceBudget::where('user_id', auth()->id())
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('finance_category_id');

        // Calculate actual spending for each category in that month
        $spending = FinanceTransaction::where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->select('finance_category_id', DB::raw('SUM(ABS(amount)) as total'))
            ->groupBy('finance_category_id')
            ->get()
            ->keyBy('finance_category_id');

        return view('pages.money-management.budgets.index', compact('categories', 'budgets', 'spending', 'month', 'year'));
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
