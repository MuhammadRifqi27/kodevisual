<?php

namespace App\Services\FinanceBtcTracking;

use App\Repositories\FinanceInvestmentTransaction\FinanceInvestmentTransactionRepository;
use App\Repositories\FinancePortfolio\FinancePortfolioRepository;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\Support\AssetBalanceAggregator;
use Illuminate\Support\Collection;

class FinanceBtcTrackingServiceImplement implements FinanceBtcTrackingService
{
    public function __construct(
        private FinancePortfolioRepository $financePortfolioRepository,
        private FinanceInvestmentTransactionRepository $investmentTransactionRepository,
        private FinanceTransactionRepository $financeTransactionRepository,
        private AssetBalanceAggregator $assetBalanceAggregator,
    ) {
    }

    public function overview(int $userId): array
    {
        $portfolios = $this->financePortfolioRepository->listByInvestmentType($userId, 'crypto');
        $portfolioIds = $portfolios->pluck('id')->all();

        $invTrx = $this->investmentTransactionRepository->forPortfolios($portfolioIds, $userId);
        $transferLegs = $this->financeTransactionRepository->assetTaggedTransferLegsForPortfolios($portfolioIds, $userId);

        $balances = $this->assetBalanceAggregator->balancesByAsset($invTrx, $transferLegs);
        $assetBalances = $balances->map(fn ($balance, $asset) => ['asset' => $asset, 'balance' => (float) $balance])->values();

        return [
            'portfolios' => $portfolios,
            'totalValue' => (float) $portfolios->sum('balance'),
            'assetBalances' => $assetBalances,
        ];
    }

    public function activityFeed(int $userId): Collection
    {
        $portfolioIds = $this->financePortfolioRepository->listByInvestmentType($userId, 'crypto')->pluck('id')->all();

        $invTrx = $this->investmentTransactionRepository->forPortfolios($portfolioIds, $userId)
            ->map(function ($item) {
                $item->source_type = 'investment';
                return $item;
            });

        $transferTrx = $this->financeTransactionRepository->assetTaggedTransferLegsForPortfolios($portfolioIds, $userId)
            ->map(function ($item) {
                $item->source_type = 'transfer';
                return $item;
            });

        return $invTrx->concat($transferTrx)->sortByDesc('date');
    }

    public function deleteLedgerEntry(int $userId, int $id): void
    {
        $this->investmentTransactionRepository->ownedOrFail($userId, $id);
        $this->investmentTransactionRepository->delete($id);
    }
}
