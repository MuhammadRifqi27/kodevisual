<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * Each transfer is stored as a pair of finance_transactions rows (out + in).
     * Only the outbound (negative-amount) leg is listed here to avoid duplicate rows;
     * the inbound leg is included as "destination".
     */
    public function index(Request $request)
    {
        $query = FinanceTransaction::where('user_id', auth()->id())
            ->where('type', 'transfer')
            ->where('amount', '<', 0)
            ->with(['portfolio', 'destinationPortfolio'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $userId = auth()->id();
        $lot = $request->filled('lot') ? (int) $request->lot : null;

        $category = FinanceCategory::firstOrCreate(
            ['name' => 'Internal Transfer', 'type' => 'expense'],
            ['description' => 'System created category for internal transfers']
        );

        $fromAccount = FinancePortfolio::with('investment')->where('user_id', $userId)->findOrFail($request->from_account_id);
        $toAccount = FinancePortfolio::with('investment')->where('user_id', $userId)->findOrFail($request->to_account_id);

        $outbound = DB::transaction(function () use ($request, $userId, $category, $fromAccount, $toAccount, $lot) {
            $from = FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $request->date,
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $request->from_account_id,
                'to_finance_investment_id' => $request->to_account_id,
                'asset' => $request->asset,
                'lot' => $lot !== null ? -$lot : null,
                'amount' => -$request->amount,
                'description' => $request->description ?? 'Transfer to ' . $toAccount->account_name . ' - ' . $toAccount->investment->name,
            ]);

            FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $request->date,
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $request->to_account_id,
                'to_finance_investment_id' => $request->from_account_id,
                'asset' => $request->asset,
                'lot' => $lot,
                'amount' => $request->amount,
                'description' => $request->description ?? 'Transfer from ' . $fromAccount->account_name . ' - ' . $fromAccount->investment->name,
            ]);

            return $from;
        });

        return response()->json($outbound->load(['portfolio', 'destinationPortfolio']), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $userId = auth()->id();
        $lot = $request->filled('lot') ? (int) $request->lot : null;

        FinancePortfolio::where('user_id', $userId)->findOrFail($request->from_account_id);
        FinancePortfolio::where('user_id', $userId)->findOrFail($request->to_account_id);

        // The list only ever surfaces the outbound (negative-amount) leg, so $id always refers to it.
        $outbound = FinanceTransaction::where('user_id', $userId)->where('type', 'transfer')->findOrFail($id);

        DB::transaction(function () use ($request, $userId, $outbound, $lot) {
            $inbound = FinanceTransaction::where('user_id', $userId)
                ->where('date', $outbound->date)
                ->where('type', 'transfer')
                ->where('finance_investment_id', $outbound->to_finance_investment_id)
                ->where('to_finance_investment_id', $outbound->finance_investment_id)
                ->where('amount', -$outbound->amount)
                ->first();

            $outbound->update([
                'date' => $request->date,
                'finance_investment_id' => $request->from_account_id,
                'to_finance_investment_id' => $request->to_account_id,
                'asset' => $request->asset,
                'lot' => $lot !== null ? -$lot : null,
                'amount' => -$request->amount,
                'description' => $request->description,
            ]);

            if ($inbound) {
                $inbound->update([
                    'date' => $request->date,
                    'finance_investment_id' => $request->to_account_id,
                    'to_finance_investment_id' => $request->from_account_id,
                    'asset' => $request->asset,
                    'lot' => $lot,
                    'amount' => $request->amount,
                    'description' => $request->description,
                ]);
            }
        });

        return response()->json($outbound->fresh(['portfolio', 'destinationPortfolio']));
    }

    public function destroy($id)
    {
        $transaction = FinanceTransaction::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($transaction) {
            FinanceTransaction::where('user_id', $transaction->user_id)
                ->where('date', $transaction->date)
                ->where('type', 'transfer')
                ->where('finance_investment_id', $transaction->to_finance_investment_id)
                ->where('to_finance_investment_id', $transaction->finance_investment_id)
                ->where('amount', -$transaction->amount)
                ->delete();

            $transaction->delete();
        });

        return response()->json(['success' => 'Transfer berhasil dihapus']);
    }
}
