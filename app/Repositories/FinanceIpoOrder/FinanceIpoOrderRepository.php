<?php

namespace App\Repositories\FinanceIpoOrder;

use App\Models\FinanceIpoOrder;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Repository;

interface FinanceIpoOrderRepository extends Repository
{
    public function ownedOrFail(int $userId, $id): FinanceIpoOrder;

    public function queryForUser(int $userId): Builder;

    /**
     * Pending (not-yet-allotted) orders for a set of portfolios — their
     * order-block transaction isn't a real holding yet, so Btc/Stock tracking
     * excludes it from the per-emiten balance breakdown.
     */
    public function pendingOrdersForPortfolios(array $portfolioIds, int $userId): \Illuminate\Support\Collection;
}
