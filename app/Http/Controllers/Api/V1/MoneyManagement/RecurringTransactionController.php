<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceRecurringTransaction\FinanceRecurringTransactionService;
use Illuminate\Http\Request;

class RecurringTransactionController extends Controller
{
    public function __construct(private FinanceRecurringTransactionService $financeRecurringTransactionService)
    {
    }

    public function index()
    {
        $this->financeRecurringTransactionService->processDue(auth()->id());

        $data = $this->financeRecurringTransactionService->listQuery(auth()->id())->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'finance_category_id' => 'required|exists:finance_categories,id',
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $recurring = $this->financeRecurringTransactionService->createRecurring(auth()->id(), $validated);

        return response()->json($recurring, 201);
    }

    public function destroy($id)
    {
        $this->financeRecurringTransactionService->deleteRecurring(auth()->id(), $id);

        return response()->json(['success' => 'Recurring transaction berhasil dihapus']);
    }

    /**
     * Generate real transactions for every due recurring template
     * (next_date <= today, is_active) and advance next_date.
     */
    public function process()
    {
        $count = $this->financeRecurringTransactionService->processDue(auth()->id());

        return response()->json(['processed' => $count]);
    }
}
