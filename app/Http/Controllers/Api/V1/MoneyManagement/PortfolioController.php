<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        $portfolios = FinancePortfolio::where('user_id', auth()->id())
            ->with('investment')
            ->get();

        return response()->json($portfolios);
    }

    public function show($id)
    {
        $portfolio = FinancePortfolio::where('user_id', auth()->id())
            ->with('investment')
            ->findOrFail($id);

        return response()->json($portfolio);
    }

    public function store(Request $request)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_investments,id',
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'account_investment' => 'nullable|boolean',
        ]);

        $portfolio = FinancePortfolio::create([
            'user_id' => auth()->id(),
            'finance_investment_id' => $request->finance_investment_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'description' => $request->description,
            'account_investment' => $request->boolean('account_investment'),
        ]);

        return response()->json($portfolio->load('investment'), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_investments,id',
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'account_investment' => 'nullable|boolean',
        ]);

        $portfolio = FinancePortfolio::where('user_id', auth()->id())->findOrFail($id);

        $portfolio->update([
            'finance_investment_id' => $request->finance_investment_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'description' => $request->description,
            'account_investment' => $request->boolean('account_investment'),
        ]);

        return response()->json($portfolio->load('investment'));
    }

    public function destroy($id)
    {
        $portfolio = FinancePortfolio::where('user_id', auth()->id())->findOrFail($id);

        if ($portfolio->generalTransactions()->exists() || $portfolio->transactions()->exists()) {
            return response()->json(['error' => 'Tidak bisa menghapus akun yang sudah memiliki riwayat transaksi'], 400);
        }

        $portfolio->delete();

        return response()->json(['success' => 'Akun Portofolio berhasil dihapus']);
    }

    /**
     * Unified activity feed for one portfolio: its internal investment ledger
     * (deposit/withdrawal/profit/loss) merged with general income/expense/transfer
     * transactions linked to it.
     */
    public function transactions($id)
    {
        $userId = auth()->id();
        FinancePortfolio::where('user_id', $userId)->findOrFail($id);

        $invTrx = FinanceInvestmentTransaction::where('finance_investment_id', $id)
            ->where('user_id', $userId)
            ->get()
            ->map(function ($item) {
                $item->source_type = 'investment';
                return $item;
            });

        $genTrx = FinanceTransaction::where('finance_investment_id', $id)
            ->where('user_id', $userId)
            ->with('category')
            ->get()
            ->map(function ($item) {
                $item->source_type = 'general';
                $item->display_amount = $item->type === 'expense' ? -$item->amount : $item->amount;
                return $item;
            });

        $data = $invTrx->concat($genTrx)->sortByDesc('date')->values();

        return response()->json($data);
    }

    public function storeTransaction(Request $request, $id)
    {
        $request->validate([
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
            'date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal,profit,loss',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $portfolio = FinancePortfolio::where('user_id', auth()->id())->findOrFail($id);

        $transaction = FinanceInvestmentTransaction::create([
            'user_id' => auth()->id(),
            'finance_investment_id' => $portfolio->id,
            'asset' => $request->asset,
            'lot' => $request->lot,
            'date' => $request->date,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return response()->json($transaction, 201);
    }

    public function updateTransaction(Request $request, $id, $trxId)
    {
        $request->validate([
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
            'date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal,profit,loss',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        FinancePortfolio::where('user_id', auth()->id())->findOrFail($id);

        $trx = FinanceInvestmentTransaction::where('user_id', auth()->id())
            ->where('finance_investment_id', $id)
            ->findOrFail($trxId);

        $trx->update($request->only('asset', 'lot', 'date', 'type', 'amount', 'description'));

        return response()->json($trx);
    }

    public function destroyTransaction($id, $trxId)
    {
        FinancePortfolio::where('user_id', auth()->id())->findOrFail($id);

        FinanceInvestmentTransaction::where('user_id', auth()->id())
            ->where('finance_investment_id', $id)
            ->findOrFail($trxId)
            ->delete();

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
