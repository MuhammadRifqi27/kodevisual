<?php

namespace App\Repositories\FinanceInvestmentTransaction;

use App\Models\FinanceInvestmentTransaction;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceInvestmentTransactionRepository extends Repository
{
    public function ownedOrFail(int $userId, $id): FinanceInvestmentTransaction;

    public function forPortfolio(int $portfolioId, int $userId): Collection;

    public function forPortfolios(array $portfolioIds, int $userId): Collection;

    /**
     * Net signed balance (deposit+profit - withdrawal-loss) for one portfolio, as of a date.
     */
    public function signedBalanceAsOf(int $portfolioId, int $userId, $asOf): float;

    /**
     * All investment transactions for a set of portfolios, excluding specific
     * transaction ids (used to exclude IPO order-blocking transactions that
     * aren't real holdings yet from Btc/Stock tracking's balance breakdown).
     */
    public function forPortfoliosExcluding(array $portfolioIds, array $excludeIds, int $userId): Collection;
}
