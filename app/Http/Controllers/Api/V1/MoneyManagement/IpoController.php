<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinanceIpoOrder;
use App\Models\FinancePortfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpoController extends Controller
{
    public function index()
    {
        $orders = FinanceIpoOrder::where('user_id', auth()->id())
            ->with('portfolio')
            ->orderBy('order_date', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'order_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot_ordered' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $userId = auth()->id();
        FinancePortfolio::where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('type', 'stock'))
            ->findOrFail($request->finance_investment_id);

        $orderAmount = $request->lot_ordered * 100 * $request->price_per_share;

        $order = DB::transaction(function () use ($request, $userId, $orderAmount) {
            $order = FinanceIpoOrder::create([
                'user_id' => $userId,
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'order_date' => $request->order_date,
                'price_per_share' => $request->price_per_share,
                'lot_ordered' => $request->lot_ordered,
                'description' => $request->description,
            ]);

            $orderTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'date' => $request->order_date,
                'type' => 'withdrawal',
                'amount' => $orderAmount,
                'description' => 'IPO Order: ' . $request->asset . ' - ' . $request->lot_ordered . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.') . ' (pending allotment)',
            ]);

            $order->update(['order_transaction_id' => $orderTrx->id]);

            return $order;
        });

        return response()->json($order->fresh('portfolio'), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'order_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot_ordered' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $order = FinanceIpoOrder::where('user_id', auth()->id())->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Pesanan yang sudah ada hasil penjatahan tidak bisa diedit. Hapus lalu buat ulang jika perlu koreksi.'], 400);
        }

        FinancePortfolio::where('user_id', auth()->id())
            ->whereHas('investment', fn ($q) => $q->where('type', 'stock'))
            ->findOrFail($request->finance_investment_id);

        $orderAmount = $request->lot_ordered * 100 * $request->price_per_share;

        DB::transaction(function () use ($request, $order, $orderAmount) {
            $order->update([
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'order_date' => $request->order_date,
                'price_per_share' => $request->price_per_share,
                'lot_ordered' => $request->lot_ordered,
                'description' => $request->description,
            ]);

            $order->orderTransaction()->update([
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'date' => $request->order_date,
                'amount' => $orderAmount,
                'description' => 'IPO Order: ' . $request->asset . ' - ' . $request->lot_ordered . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.') . ' (pending allotment)',
            ]);
        });

        return response()->json($order->fresh('portfolio'));
    }

    public function confirmAllotment(Request $request, $id)
    {
        $request->validate([
            'lot_allotted' => 'required|integer|min:0',
            'allotment_date' => 'required|date',
        ]);

        $order = FinanceIpoOrder::where('user_id', auth()->id())->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Pesanan ini sudah punya hasil penjatahan'], 400);
        }

        if ($request->lot_allotted > $order->lot_ordered) {
            return response()->json(['error' => 'Lot allotted tidak boleh lebih besar dari lot yang dipesan'], 400);
        }

        $userId = auth()->id();
        $orderAmount = $order->order_amount;
        $allottedAmount = $request->lot_allotted * 100 * $order->price_per_share;

        DB::transaction(function () use ($request, $order, $userId, $orderAmount, $allottedAmount) {
            $releaseTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $order->finance_investment_id,
                'asset' => $order->asset,
                'date' => $request->allotment_date,
                'type' => 'deposit',
                'amount' => $orderAmount,
                'description' => 'IPO Block Released: ' . $order->asset,
            ]);

            $holdingTrxId = null;
            $offsetTrxId = null;

            if ($request->lot_allotted > 0) {
                $holdingTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $order->finance_investment_id,
                    'asset' => $order->asset,
                    'lot' => $request->lot_allotted,
                    'date' => $request->allotment_date,
                    'type' => 'deposit',
                    'amount' => $allottedAmount,
                    'description' => 'IPO Allotment: ' . $order->asset . ' - ' . $request->lot_allotted . '/' . $order->lot_ordered . ' lot @ Rp ' . number_format($order->price_per_share, 0, ',', '.'),
                ]);
                $holdingTrxId = $holdingTrx->id;

                $offsetTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $order->finance_investment_id,
                    'date' => $request->allotment_date,
                    'type' => 'withdrawal',
                    'amount' => $allottedAmount,
                    'description' => 'IPO Allotment funded from broker balance: ' . $order->asset,
                ]);
                $offsetTrxId = $offsetTrx->id;
            }

            $order->update([
                'lot_allotted' => $request->lot_allotted,
                'allotment_date' => $request->allotment_date,
                'release_transaction_id' => $releaseTrx->id,
                'holding_transaction_id' => $holdingTrxId,
                'offset_transaction_id' => $offsetTrxId,
            ]);
        });

        return response()->json($order->fresh('portfolio'));
    }

    public function destroy($id)
    {
        $order = FinanceIpoOrder::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($order) {
            FinanceInvestmentTransaction::whereIn('id', array_filter([
                $order->order_transaction_id,
                $order->release_transaction_id,
                $order->holding_transaction_id,
                $order->offset_transaction_id,
            ]))->delete();

            $order->delete();
        });

        return response()->json(['success' => 'Pesanan IPO berhasil dihapus']);
    }
}
