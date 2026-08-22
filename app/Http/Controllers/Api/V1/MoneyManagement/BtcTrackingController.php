<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;

class BtcTrackingController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $btcPortfolios = FinancePortfolio::where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('type', 'crypto'))
            ->get();

        $totalBtcValue = $btcPortfolios->sum('balance');
        $portfolioIds = $btcPortfolios->pluck('id');

        $balances = FinanceInvestmentTransaction::where('user_id', $userId)
            ->whereIn('finance_investment_id', $portfolioIds)
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

        $assetBalances = $balances->map(fn ($balance, $asset) => ['asset' => $asset, 'balance' => (float) $balance])->values();

        return response()->json([
            'btc_portfolios' => $btcPortfolios,
            'total_btc_value' => (float) $totalBtcValue,
            'asset_balances' => $assetBalances,
        ]);
    }

    public function destroy($id)
    {
        FinanceInvestmentTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();

        return response()->json(['success' => 'Transaction removed from tracking']);
    }
}
