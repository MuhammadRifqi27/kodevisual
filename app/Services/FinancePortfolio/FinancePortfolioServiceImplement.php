<?php

namespace App\Services\FinancePortfolio;

use App\Exceptions\FinanceDomainException;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinancePortfolio;
use App\Repositories\FinanceInvestmentTransaction\FinanceInvestmentTransactionRepository;
use App\Repositories\FinancePortfolio\FinancePortfolioRepository;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Service;

class FinancePortfolioServiceImplement extends Service implements FinancePortfolioService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(
        FinancePortfolioRepository $mainRepository,
        private FinanceInvestmentTransactionRepository $investmentTransactionRepository,
        private FinanceTransactionRepository $transactionRepository,
    ) {
        $this->mainRepository = $mainRepository;
    }

    public function assertOwned(int $userId, int $portfolioId): FinancePortfolio
    {
        return $this->mainRepository->ownedOrFail($userId, $portfolioId);
    }

    public function assertOwnedStockPortfolio(int $userId, int $portfolioId): FinancePortfolio
    {
        return $this->mainRepository->ownedStockPortfolioOrFail($userId, $portfolioId);
    }

    public function listForUser(int $userId): Collection
    {
        return $this->mainRepository->listWithInvestment($userId);
    }

    public function createPortfolio(int $userId, array $data): FinancePortfolio
    {
        $data['user_id'] = $userId;
        $data['account_investment'] = (bool) ($data['account_investment'] ?? false);

        return $this->mainRepository->create($data);
    }

    public function updatePortfolio(int $userId, int $portfolioId, array $data): FinancePortfolio
    {
        $this->assertOwned($userId, $portfolioId);

        $data['account_investment'] = (bool) ($data['account_investment'] ?? false);
        $this->mainRepository->update($portfolioId, $data);

        return $this->mainRepository->find($portfolioId);
    }

    public function deletePortfolio(int $userId, int $portfolioId): void
    {
        $this->assertOwned($userId, $portfolioId);

        if ($this->mainRepository->hasAnyTransactions($portfolioId)) {
            throw new FinanceDomainException('Tidak bisa menghapus akun yang sudah memiliki riwayat transaksi');
        }

        $this->mainRepository->delete($portfolioId);
    }

    public function activityFeed(int $userId, int $portfolioId): Collection
    {
        $this->assertOwned($userId, $portfolioId);

        $invTrx = $this->investmentTransactionRepository->forPortfolio($portfolioId, $userId)
            ->map(function ($item) {
                $item->source_type = 'investment';
                return $item;
            });

        $genTrx = $this->transactionRepository->forPortfolio($portfolioId, $userId)
            ->map(function ($item) {
                $item->source_type = 'general';
                $item->display_amount = $item->type === 'expense' ? -$item->amount : $item->amount;
                return $item;
            });

        return $invTrx->concat($genTrx)->sortByDesc('date')->values();
    }

    public function createLedgerEntry(int $userId, int $portfolioId, array $data): FinanceInvestmentTransaction
    {
        $this->assertOwned($userId, $portfolioId);

        $data['user_id'] = $userId;
        $data['finance_investment_id'] = $portfolioId;

        return $this->investmentTransactionRepository->create($data);
    }

    public function updateLedgerEntry(int $userId, int $trxId, array $data, ?int $expectedPortfolioId = null): FinanceInvestmentTransaction
    {
        $trx = $this->investmentTransactionRepository->ownedOrFail($userId, $trxId);
        $this->assertBelongsToPortfolio($trx, $expectedPortfolioId);

        $this->investmentTransactionRepository->update($trxId, $data);

        return $this->investmentTransactionRepository->find($trxId);
    }

    public function deleteLedgerEntry(int $userId, int $trxId, ?int $expectedPortfolioId = null): void
    {
        $trx = $this->investmentTransactionRepository->ownedOrFail($userId, $trxId);
        $this->assertBelongsToPortfolio($trx, $expectedPortfolioId);

        $this->investmentTransactionRepository->delete($trxId);
    }

    private function assertBelongsToPortfolio(FinanceInvestmentTransaction $trx, ?int $expectedPortfolioId): void
    {
        if ($expectedPortfolioId !== null && (int) $trx->finance_investment_id !== $expectedPortfolioId) {
            throw new FinanceDomainException('Transaksi tidak ditemukan pada akun ini');
        }
    }
}
