<?php

namespace App\Repositories\FinanceEmitenTrade;

use App\Models\FinanceEmitenTrade;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceEmitenTradeRepository extends Repository
{
    public function ownedOrFail(int $userId, $id): FinanceEmitenTrade;

    /**
     * Flat, de-duplicated list of every FinanceInvestmentTransaction id linked
     * to a trade (holding/cash/pnl) across a set of portfolios — used to hide
     * the "delete" action on tracking rows that must be managed via the trade instead.
     */
    public function linkedTransactionIdsForPortfolios(array $portfolioIds, int $userId): Collection;
}
