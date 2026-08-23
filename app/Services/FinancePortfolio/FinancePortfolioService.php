<?php

namespace App\Services\FinancePortfolio;

use App\Models\FinanceInvestmentTransaction;
use App\Models\FinancePortfolio;
use Illuminate\Support\Collection;
use LaravelEasyRepository\BaseService;

interface FinancePortfolioService extends BaseService
{
    /**
     * Shared ownership guard — injected by other Services to assert a
     * finance_investment_id from request input actually belongs to the user.
     */
    public function assertOwned(int $userId, int $portfolioId): FinancePortfolio;

    /**
     * Same as assertOwned, but additionally requires the portfolio's investment
     * to be of type 'stock' — used by IPO orders and stock trading.
     */
    public function assertOwnedStockPortfolio(int $userId, int $portfolioId): FinancePortfolio;

    public function listForUser(int $userId): Collection;

    public function createPortfolio(int $userId, array $data): FinancePortfolio;

    public function updatePortfolio(int $userId, int $portfolioId, array $data): FinancePortfolio;

    /**
     * @throws \App\Exceptions\FinanceDomainException if the portfolio already has ledger history.
     */
    public function deletePortfolio(int $userId, int $portfolioId): void;

    /**
     * Unified ledger: investment transactions + general transactions linked to this portfolio.
     */
    public function activityFeed(int $userId, int $portfolioId): Collection;

    public function createLedgerEntry(int $userId, int $portfolioId, array $data): FinanceInvestmentTransaction;

    /**
     * @param int|null $expectedPortfolioId When given (API's nested-resource routes), also
     *   asserts the transaction actually belongs to this portfolio, not just to the user.
     * @throws \App\Exceptions\FinanceDomainException if $expectedPortfolioId doesn't match.
     */
    public function updateLedgerEntry(int $userId, int $trxId, array $data, ?int $expectedPortfolioId = null): FinanceInvestmentTransaction;

    public function deleteLedgerEntry(int $userId, int $trxId, ?int $expectedPortfolioId = null): void;
}
