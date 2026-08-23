<?php

namespace App\Repositories\FinancePortfolio;

use App\Models\FinancePortfolio;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinancePortfolioRepository extends Repository
{
    public function ownedOrFail(int $userId, $id): FinancePortfolio;

    /**
     * Same as ownedOrFail, but additionally requires the portfolio's investment
     * to be of type 'stock' — used by IPO orders and stock trading, which only
     * make sense against a brokerage-type account.
     */
    public function ownedStockPortfolioOrFail(int $userId, $id): FinancePortfolio;

    public function listWithInvestment(int $userId): Collection;

    /**
     * Portfolios whose linked FinanceInvestment is of a given type (crypto/stock/other).
     */
    public function listByInvestmentType(int $userId, string $type): Collection;

    public function hasAnyTransactions(int $portfolioId): bool;

    /**
     * Fuzzy account-name lookup (e.g. "%Bank BNI%") — used by Wedding Planner
     * to surface a specific bank balance by name-matching. Fragile string-match
     * coupling to Money Management's portfolio naming, carried over as-is.
     */
    public function findByAccountNameLike(int $userId, string $pattern): ?FinancePortfolio;
}
