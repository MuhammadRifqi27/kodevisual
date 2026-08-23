<?php

namespace App\Services\Support;

use Illuminate\Support\Collection;

/**
 * Plain support class (no repository, no interface/binding) shared by
 * BtcTrackingService and StockTrackingService to group per-asset ledger
 * balances — this logic was previously copy-pasted independently in both
 * the web and API Btc/Stock tracking controllers.
 */
class AssetBalanceAggregator
{
    /**
     * @param Collection $investmentTransactions FinanceInvestmentTransaction rows (any asset).
     * @param Collection $transferLegs Asset-tagged FinanceTransaction rows of type 'transfer' (already signed).
     * @return Collection Keyed by asset name, value is the signed balance.
     */
    public function balancesByAsset(Collection $investmentTransactions, Collection $transferLegs): Collection
    {
        $balances = $investmentTransactions
            ->groupBy(fn ($item) => $item->asset ?: 'Unspecified')
            ->map(fn ($rows) => $rows->sum(fn ($row) => in_array($row->type, ['deposit', 'profit']) ? $row->amount : -$row->amount));

        $transferBalances = $transferLegs
            ->groupBy('asset')
            ->map(fn ($rows) => $rows->sum('amount'));

        foreach ($transferBalances as $asset => $amount) {
            $balances[$asset] = ($balances[$asset] ?? 0) + $amount;
        }

        return $balances;
    }

    /**
     * @param Collection $investmentTransactions Any asset; rows without a lot are ignored.
     * @param Collection $transferLegs Any asset; rows without a lot are ignored.
     * @return Collection Keyed by asset name, value is the signed lot count.
     */
    public function lotsByAsset(Collection $investmentTransactions, Collection $transferLegs): Collection
    {
        $lots = $investmentTransactions->whereNotNull('lot')
            ->groupBy(fn ($item) => $item->asset ?: 'Unspecified')
            ->map(fn ($rows) => $rows->sum(fn ($row) => in_array($row->type, ['deposit', 'profit']) ? $row->lot : -$row->lot));

        $transferLots = $transferLegs->whereNotNull('lot')
            ->groupBy('asset')
            ->map(fn ($rows) => $rows->sum('lot'));

        foreach ($transferLots as $asset => $lot) {
            $lots[$asset] = ($lots[$asset] ?? 0) + $lot;
        }

        return $lots;
    }
}
