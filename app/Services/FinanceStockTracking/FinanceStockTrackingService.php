<?php

namespace App\Services\FinanceStockTracking;

use App\Models\FinanceEmitenPrice;
use App\Models\FinanceEmitenTrade;
use Illuminate\Support\Collection;

/**
 * Not model-backed (aggregates several models: portfolios, investment
 * transactions, transfers, IPO orders, trades, prices), so this deliberately
 * does not extend LaravelEasyRepository\BaseService.
 */
interface FinanceStockTrackingService
{
    public function overview(int $userId): array;

    /**
     * @return array{data: Collection, ipoTrxIds: Collection, tradeTrxIds: Collection}
     */
    public function activityFeed(int $userId): array;

    public function deleteLedgerEntry(int $userId, int $id): void;

    public function updatePrice(int $userId, string $asset, float $price): FinanceEmitenPrice;

    /**
     * @throws \App\Exceptions\FinanceDomainException on insufficient trading balance (buy)
     *   or insufficient lot held (sell).
     */
    public function recordTrade(int $userId, array $data): FinanceEmitenTrade;

    public function deleteTrade(int $userId, int $id): void;
}
