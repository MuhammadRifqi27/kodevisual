<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceBudget\FinanceBudgetService;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(private FinanceBudgetService $financeBudgetService)
    {
    }

    public function index(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        $overview = $this->financeBudgetService->overview(auth()->id(), $month, $year);

        return view('pages.money-management.budgets.index', $overview);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'finance_category_id' => 'required|exists:finance_categories,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        $this->financeBudgetService->upsert(
            auth()->id(),
            $validated['finance_category_id'],
            $validated['month'],
            $validated['year'],
            $validated['amount']
        );

        return response()->json(['success' => 'Budget berhasil disimpan']);
    }
}
