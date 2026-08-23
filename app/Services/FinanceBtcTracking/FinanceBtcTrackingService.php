<?php

namespace App\Services\FinanceBtcTracking;

use Illuminate\Support\Collection;

/**
 * Not model-backed (aggregates portfolios + two transaction models), so this
 * deliberately does not extend LaravelEasyRepository\BaseService.
 */
interface FinanceBtcTrackingService
{
    /**
     * @return array{portfolios: Collection, totalValue: float, assetBalances: Collection}
     */
    public function overview(int $userId): array;

    /**
     * Unified ledger feed (investment transactions + asset-tagged transfer legs)
     * for the user's crypto portfolios, tagged with source_type.
     */
    public function activityFeed(int $userId): Collection;

    public function deleteLedgerEntry(int $userId, int $id): void;
}
