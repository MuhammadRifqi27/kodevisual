<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * List income/expense transactions (transfers are excluded - see TransferController).
     * Supports filtering by type, category_id, and a date range, plus pagination.
     */
    public function index(Request $request)
    {
        $query = FinanceTransaction::where('user_id', auth()->id())
            ->whereIn('type', ['income', 'expense'])
            ->with(['category', 'portfolio']);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('finance_category_id', $request->category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $transactions,
            'summary' => [
                'total_income' => (float) $totalIncome,
                'total_expense' => (float) $totalExpense,
                'net_balance' => (float) ($totalIncome - $totalExpense),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($request->investment_id) {
            FinancePortfolio::where('user_id', auth()->id())->findOrFail($request->investment_id);
        }

        $transaction = FinanceTransaction::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'type' => $request->type,
            'finance_category_id' => $request->category_id,
            'finance_investment_id' => $request->investment_id,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return response()->json($transaction->load(['category', 'portfolio']), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($request->investment_id) {
            FinancePortfolio::where('user_id', auth()->id())->findOrFail($request->investment_id);
        }

        $transaction = FinanceTransaction::where('user_id', auth()->id())->findOrFail($id);
        $transaction->update([
            'date' => $request->date,
            'type' => $request->type,
            'finance_category_id' => $request->category_id,
            'finance_investment_id' => $request->investment_id,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return response()->json($transaction->load(['category', 'portfolio']));
    }

    public function destroy($id)
    {
        FinanceTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
