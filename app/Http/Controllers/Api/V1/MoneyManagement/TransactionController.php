<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceTransaction\FinanceTransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private FinanceTransactionService $financeTransactionService)
    {
    }

    /**
     * List income/expense transactions (transfers are excluded - see TransferController).
     * Supports filtering by type, category_id, and a date range, plus pagination.
     */
    public function index(Request $request)
    {
        $query = $this->financeTransactionService->filteredQuery(auth()->id(), $request->only([
            'type', 'category_id', 'start_date', 'end_date',
        ]));

        $summary = $this->financeTransactionService->summaryFor($query);

        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $transactions,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $transaction = $this->financeTransactionService->createTransaction(auth()->id(), $validated);

        return response()->json($transaction->load(['category', 'portfolio']), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $transaction = $this->financeTransactionService->updateTransaction(auth()->id(), $id, $validated);

        return response()->json($transaction->load(['category', 'portfolio']));
    }

    public function destroy($id)
    {
        $this->financeTransactionService->deleteTransaction(auth()->id(), $id);

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
