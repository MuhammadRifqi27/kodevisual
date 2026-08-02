<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceEmitenPrice;
use App\Models\FinanceEmitenTrade;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinanceIpoOrder;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTrackingController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $stockPortfolios = FinancePortfolio::where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('type', 'stock'))
            ->get();

        $totalStockValue = $stockPortfolios->sum('balance');
        $portfolioIds = $stockPortfolios->pluck('id');

        $pendingOrders = FinanceIpoOrder::where('user_id', $userId)
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNull('lot_allotted')
            ->get();
        $pendingOrderTrxIds = $pendingOrders->pluck('order_transaction_id')->filter()->values();
        $openAmount = $pendingOrders->sum('order_amount');

        $balances = FinanceInvestmentTransaction::where('user_id', $userId)
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotIn('id', $pendingOrderTrxIds)
            ->get()
            ->groupBy(fn ($item) => $item->asset ?: 'Unspecified')
            ->map(fn ($rows) => $rows->sum(fn ($row) => in_array($row->type, ['deposit', 'profit']) ? $row->amount : -$row->amount));

        $transferBalances = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'transfer')
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotNull('asset')
            ->get()
            ->groupBy('asset')
            ->map(fn ($rows) => $rows->sum('amount'));

        foreach ($transferBalances as $asset => $amount) {
            $balances[$asset] = ($balances[$asset] ?? 0) + $amount;
        }

        $lots = FinanceInvestmentTransaction::where('user_id', $userId)
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotIn('id', $pendingOrderTrxIds)
            ->whereNotNull('lot')
            ->get()
            ->groupBy(fn ($item) => $item->asset ?: 'Unspecified')
            ->map(fn ($rows) => $rows->sum(fn ($row) => in_array($row->type, ['deposit', 'profit']) ? $row->lot : -$row->lot));

        $transferLots = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'transfer')
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotNull('lot')
            ->get()
            ->groupBy('asset')
            ->map(fn ($rows) => $rows->sum('lot'));

        foreach ($transferLots as $asset => $lot) {
            $lots[$asset] = ($lots[$asset] ?? 0) + $lot;
        }

        $prices = FinanceEmitenPrice::where('user_id', $userId)->pluck('current_price', 'asset');

        $emitenBalances = $balances->keys()->merge($lots->keys())->unique()
            ->reject(fn ($asset) => $asset === 'Unspecified')
            ->map(function ($asset) use ($balances, $lots, $prices) {
                $balance = $balances[$asset] ?? 0;
                $lot = $lots[$asset] ?? null;
                $price = $prices[$asset] ?? null;
                $shares = $lot !== null ? $lot * 100 : null;
                $marketValue = ($price !== null && $shares !== null) ? $shares * $price : $balance;
                return [
                    'asset' => $asset,
                    'balance' => (float) $balance,
                    'lot' => $lot,
                    'current_price' => $price !== null ? (float) $price : null,
                    'market_value' => (float) $marketValue,
                    'pnl' => (float) ($marketValue - $balance),
                ];
            })->values();

        $namedEmitenTotal = $balances->sum() - ($balances['Unspecified'] ?? 0);
        $tradingBalance = $totalStockValue - $namedEmitenTotal;
        $investedTotal = $namedEmitenTotal;
        $totalPnl = $emitenBalances->sum('pnl');
        $totalEquity = $tradingBalance + $openAmount + $investedTotal + $totalPnl;

        return response()->json([
            'stock_portfolios' => $stockPortfolios,
            'total_stock_value' => (float) $totalStockValue,
            'emiten_balances' => $emitenBalances,
            'trading_balance' => (float) $tradingBalance,
            'open_amount' => (float) $openAmount,
            'invested_total' => (float) $investedTotal,
            'total_pnl' => (float) $totalPnl,
            'total_equity' => (float) $totalEquity,
        ]);
    }

    public function destroy($id)
    {
        FinanceInvestmentTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();

        return response()->json(['success' => 'Transaction removed from tracking']);
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'asset' => 'required|string|max:100',
            'current_price' => 'required|numeric|min:0.01',
        ]);

        $price = FinanceEmitenPrice::updateOrCreate(
            ['user_id' => auth()->id(), 'asset' => $request->asset],
            ['current_price' => $request->current_price]
        );

        return response()->json($price);
    }

    public function storeTrade(Request $request)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'type' => 'required|in:buy,sell',
            'trade_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $userId = auth()->id();
        $portfolio = FinancePortfolio::where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('type', 'stock'))
            ->findOrFail($request->finance_investment_id);

        $amount = $request->lot * 100 * $request->price_per_share;

        if ($request->type === 'buy') {
            $tradingBalance = $this->portfolioTradingBalance($portfolio->id, $userId);
            if ($amount > $tradingBalance) {
                return response()->json(['error' => 'Trading balance tidak cukup. Tersedia Rp ' . number_format($tradingBalance, 0, ',', '.')], 400);
            }

            $trade = DB::transaction(function () use ($request, $userId, $portfolio, $amount) {
                $holdingTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'asset' => $request->asset,
                    'lot' => $request->lot,
                    'date' => $request->trade_date,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'description' => 'Buy ' . $request->asset . ': ' . $request->lot . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.'),
                ]);

                $cashTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'date' => $request->trade_date,
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'description' => 'Buy ' . $request->asset . ': funded from trading balance',
                ]);

                return FinanceEmitenTrade::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'asset' => $request->asset,
                    'type' => 'buy',
                    'trade_date' => $request->trade_date,
                    'price_per_share' => $request->price_per_share,
                    'lot' => $request->lot,
                    'description' => $request->description,
                    'holding_transaction_id' => $holdingTrx->id,
                    'cash_transaction_id' => $cashTrx->id,
                ]);
            });

            return response()->json($trade, 201);
        }

        [$currentLot, $currentInvested] = $this->portfolioEmitenState($portfolio->id, $request->asset, $userId);

        if ($request->lot > $currentLot) {
            return response()->json(['error' => 'Lot tidak cukup. Anda hanya punya ' . $currentLot . ' lot ' . $request->asset], 400);
        }

        $costOfGoodsSold = $currentLot > 0 ? ($currentInvested * $request->lot / $currentLot) : 0;
        $realizedPnl = $amount - $costOfGoodsSold;

        $trade = DB::transaction(function () use ($request, $userId, $portfolio, $costOfGoodsSold, $realizedPnl) {
            $holdingTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolio->id,
                'asset' => $request->asset,
                'lot' => $request->lot,
                'date' => $request->trade_date,
                'type' => 'withdrawal',
                'amount' => $costOfGoodsSold,
                'description' => 'Sell ' . $request->asset . ': ' . $request->lot . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.'),
            ]);

            $cashTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolio->id,
                'date' => $request->trade_date,
                'type' => 'deposit',
                'amount' => $costOfGoodsSold,
                'description' => 'Sell ' . $request->asset . ': capital returned to trading balance',
            ]);

            $pnlTrxId = null;
            if (abs($realizedPnl) > 0.004) {
                $pnlTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'date' => $request->trade_date,
                    'type' => $realizedPnl > 0 ? 'profit' : 'loss',
                    'amount' => abs($realizedPnl),
                    'description' => 'Sell ' . $request->asset . ': realized ' . ($realizedPnl > 0 ? 'profit' : 'loss'),
                ]);
                $pnlTrxId = $pnlTrx->id;
            }

            return FinanceEmitenTrade::create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolio->id,
                'asset' => $request->asset,
                'type' => 'sell',
                'trade_date' => $request->trade_date,
                'price_per_share' => $request->price_per_share,
                'lot' => $request->lot,
                'description' => $request->description,
                'holding_transaction_id' => $holdingTrx->id,
                'cash_transaction_id' => $cashTrx->id,
                'pnl_transaction_id' => $pnlTrxId,
            ]);
        });

        return response()->json($trade, 201);
    }

    public function destroyTrade($id)
    {
        $trade = FinanceEmitenTrade::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function () use ($trade) {
            FinanceInvestmentTransaction::whereIn('id', array_filter([
                $trade->holding_transaction_id,
                $trade->cash_transaction_id,
                $trade->pnl_transaction_id,
            ]))->delete();

            $trade->delete();
        });

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }

    private function portfolioPendingOrderTrxIds($portfolioId, $userId)
    {
        return FinanceIpoOrder::where('user_id', $userId)
            ->where('finance_investment_id', $portfolioId)
            ->whereNull('lot_allotted')
            ->pluck('order_transaction_id')
            ->filter()
            ->values();
    }

    private function portfolioNamedEmitenBalances($portfolioId, $userId)
    {
        $pendingOrderTrxIds = $this->portfolioPendingOrderTrxIds($portfolioId, $userId);

        $balances = FinanceInvestmentTransaction::where('user_id', $userId)
            ->where('finance_investment_id', $portfolioId)
            ->whereNotIn('id', $pendingOrderTrxIds)
            ->get()
            ->groupBy(fn ($item) => $item->asset ?: 'Unspecified')
            ->map(fn ($rows) => $rows->sum(fn ($row) => in_array($row->type, ['deposit', 'profit']) ? $row->amount : -$row->amount));

        $transferBalances = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'transfer')
            ->where('finance_investment_id', $portfolioId)
            ->whereNotNull('asset')
            ->get()
            ->groupBy('asset')
            ->map(fn ($rows) => $rows->sum('amount'));

        foreach ($transferBalances as $asset => $amount) {
            $balances[$asset] = ($balances[$asset] ?? 0) + $amount;
        }

        return $balances;
    }

    private function portfolioTradingBalance($portfolioId, $userId)
    {
        $portfolio = FinancePortfolio::findOrFail($portfolioId);
        $balances = $this->portfolioNamedEmitenBalances($portfolioId, $userId);
        $namedEmitenTotal = $balances->sum() - ($balances['Unspecified'] ?? 0);
        return $portfolio->balance - $namedEmitenTotal;
    }

    private function portfolioEmitenState($portfolioId, $asset, $userId)
    {
        $balances = $this->portfolioNamedEmitenBalances($portfolioId, $userId);
        $invested = $balances[$asset] ?? 0;

        $pendingOrderTrxIds = $this->portfolioPendingOrderTrxIds($portfolioId, $userId);

        $lots = FinanceInvestmentTransaction::where('user_id', $userId)
            ->where('finance_investment_id', $portfolioId)
            ->whereNotIn('id', $pendingOrderTrxIds)
            ->whereNotNull('lot')
            ->get()
            ->groupBy(fn ($item) => $item->asset ?: 'Unspecified')
            ->map(fn ($rows) => $rows->sum(fn ($row) => in_array($row->type, ['deposit', 'profit']) ? $row->lot : -$row->lot));

        $transferLots = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'transfer')
            ->where('finance_investment_id', $portfolioId)
            ->whereNotNull('lot')
            ->get()
            ->groupBy('asset')
            ->map(fn ($rows) => $rows->sum('lot'));

        foreach ($transferLots as $a => $lot) {
            $lots[$a] = ($lots[$a] ?? 0) + $lot;
        }

        return [$lots[$asset] ?? 0, $invested];
    }
}
