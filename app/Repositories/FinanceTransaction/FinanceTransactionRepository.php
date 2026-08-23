<?php

namespace App\Repositories\FinanceTransaction;

use App\Models\FinanceTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceTransactionRepository extends Repository
{
    public function ownedOrFail(int $userId, $id): FinanceTransaction;

    /**
     * General (income/expense/transfer) transactions linked to one portfolio,
     * with their category eager-loaded — used to build a portfolio's unified ledger feed.
     */
    public function forPortfolio(int $portfolioId, int $userId): Collection;

    /**
     * Income/expense transactions for a user, with optional type/category/date-range filters.
     * @param array{type?: ?string, category_id?: ?string, start_date?: ?string, end_date?: ?string} $filters
     */
    public function filteredQuery(int $userId, array $filters = []): Builder;

    /**
     * Outbound (negative-amount) transfer legs only, to avoid duplicate rows in a listing.
     */
    public function transferLegsQuery(int $userId): Builder;

    /**
     * The other leg of a transfer pair, matched heuristically since there is
     * no persisted FK linking the two legs (date + type + swapped accounts + negated amount).
     */
    public function findTransferCounterpart(FinanceTransaction $leg): ?FinanceTransaction;

    public function sumByTypeBetween(int $userId, string $type, $start, $end): float;

    /**
     * @return Collection Keyed by finance_category_id, each row has ->total.
     */
    public function groupedByCategoryBetween(int $userId, string $type, $start, $end): Collection;

    /**
     * Signed balance (income +, expense -, transfer already signed) for one
     * portfolio, as of a date — used by the dashboard's net-worth calculation.
     */
    public function signedBalanceAsOf(int $portfolioId, int $userId, $asOf): float;

    /**
     * Same as signedBalanceAsOf, but for transactions not linked to any
     * portfolio ("untracked cash").
     */
    public function untrackedCashAsOf(int $userId, $asOf): float;

    /**
     * Top N categories by total amount in a date range, with each category's
     * display name resolved (falls back to $fallbackName if uncategorized).
     * @return Collection [['name' => string, 'total' => float], ...]
     */
    public function topCategoriesBetween(int $userId, string $type, $start, $end, int $limit, string $fallbackName): Collection;

    /**
     * Expense total grouped by category name (not id) in a date range, ordered
     * by total desc — used by the Summary page's category breakdown table.
     * @return Collection Rows with ->name and ->total.
     */
    public function expenseByCategoryNameBetween(int $userId, $start, $end): Collection;

    /**
     * @return array{income: float, expense: float}
     */
    public function incomeExpenseBetween(int $userId, $start, $end): array;

    public function recentBetween(int $userId, $start, $end, int $limit): Collection;

    /**
     * Asset-tagged transfer legs (type='transfer', asset not null) for a set of
     * portfolios — used to merge into Btc/Stock tracking's per-asset ledger feed.
     */
    public function assetTaggedTransferLegsForPortfolios(array $portfolioIds, int $userId): Collection;

    /**
     * All transfer legs for a set of portfolios (no asset/lot filter — caller
     * derives both the asset-tagged and lot-tagged subsets from this one set).
     */
    public function transferLegsForPortfolios(array $portfolioIds, int $userId): Collection;
}
