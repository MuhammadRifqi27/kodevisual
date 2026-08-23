<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Exceptions\FinanceDomainException;
use App\Http\Controllers\Controller;
use App\Services\FinanceStockTracking\FinanceStockTrackingService;
use Illuminate\Http\Request;

class StockTrackingController extends Controller
{
    public function __construct(private FinanceStockTrackingService $financeStockTrackingService)
    {
    }

    public function index()
    {
        $overview = $this->financeStockTrackingService->overview(auth()->id());

        return response()->json([
            'stock_portfolios' => $overview['stockPortfolios'],
            'total_stock_value' => $overview['totalStockValue'],
            'emiten_balances' => $overview['emitenBalances'],
            'trading_balance' => $overview['tradingBalance'],
            'open_amount' => $overview['openAmount'],
            'invested_total' => $overview['investedTotal'],
            'total_pnl' => $overview['totalPnl'],
            'total_equity' => $overview['totalEquity'],
        ]);
    }

    public function destroy($id)
    {
        $this->financeStockTrackingService->deleteLedgerEntry(auth()->id(), $id);

        return response()->json(['success' => 'Transaction removed from tracking']);
    }

    public function updatePrice(Request $request)
    {
        $validated = $request->validate([
            'asset' => 'required|string|max:100',
            'current_price' => 'required|numeric|min:0.01',
        ]);

        $price = $this->financeStockTrackingService->updatePrice(auth()->id(), $validated['asset'], $validated['current_price']);

        return response()->json($price);
    }

    public function storeTrade(Request $request)
    {
        $validated = $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'type' => 'required|in:buy,sell',
            'trade_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        try {
            $trade = $this->financeStockTrackingService->recordTrade(auth()->id(), $validated);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($trade, 201);
    }

    public function destroyTrade($id)
    {
        $this->financeStockTrackingService->deleteTrade(auth()->id(), $id);

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
