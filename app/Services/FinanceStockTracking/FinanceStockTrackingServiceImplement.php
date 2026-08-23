<?php

namespace App\Services\FinanceStockTracking;

use App\Exceptions\FinanceDomainException;
use App\Models\FinanceEmitenPrice;
use App\Models\FinanceEmitenTrade;
use App\Repositories\FinanceEmitenPrice\FinanceEmitenPriceRepository;
use App\Repositories\FinanceEmitenTrade\FinanceEmitenTradeRepository;
use App\Repositories\FinanceInvestmentTransaction\FinanceInvestmentTransactionRepository;
use App\Repositories\FinanceIpoOrder\FinanceIpoOrderRepository;
use App\Repositories\FinancePortfolio\FinancePortfolioRepository;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\FinancePortfolio\FinancePortfolioService;
use App\Services\Support\AssetBalanceAggregator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceStockTrackingServiceImplement implements FinanceStockTrackingService
{
    public function __construct(
        private FinancePortfolioRepository $financePortfolioRepository,
        private FinanceInvestmentTransactionRepository $investmentTransactionRepository,
        private FinanceTransactionRepository $financeTransactionRepository,
        private FinanceIpoOrderRepository $financeIpoOrderRepository,
        private FinanceEmitenTradeRepository $financeEmitenTradeRepository,
        private FinanceEmitenPriceRepository $financeEmitenPriceRepository,
        private FinancePortfolioService $financePortfolioService,
        private AssetBalanceAggregator $assetBalanceAggregator,
    ) {
    }

    public function overview(int $userId): array
    {
        $stockPortfolios = $this->financePortfolioRepository->listByInvestmentType($userId, 'stock');
        $portfolioIds = $stockPortfolios->pluck('id')->all();

        $pendingOrders = $this->financeIpoOrderRepository->pendingOrdersForPortfolios($portfolioIds, $userId);
        $pendingOrderTrxIds = $pendingOrders->pluck('order_transaction_id')->filter()->values()->all();
        $openAmount = $pendingOrders->sum('order_amount');

        $invTrx = $this->investmentTransactionRepository->forPortfoliosExcluding($portfolioIds, $pendingOrderTrxIds, $userId);
        $transferLegs = $this->financeTransactionRepository->transferLegsForPortfolios($portfolioIds, $userId);

        $balances = $this->assetBalanceAggregator->balancesByAsset($invTrx, $transferLegs->whereNotNull('asset'));
        $lots = $this->assetBalanceAggregator->lotsByAsset($invTrx, $transferLegs);

        $prices = $this->financeEmitenPriceRepository->pricesForUser($userId);

        $emitenBalances = $balances->keys()->merge($lots->keys())->unique()
            ->reject(fn ($asset) => $asset === 'Unspecified')
            ->map(function ($asset) use ($balances, $lots, $prices) {
                $balance = $balances[$asset] ?? 0;
                $lot = $lots[$asset] ?? null;
                $price = $prices[$asset] ?? null;
                $shares = $lot !== null ? $lot * 100 : null;
                $marketValue = ($price !== null && $shares !== null) ? $shares * $price : $balance;

                return [
                    'asset' => $asset,
                    'balance' => (float) $balance,
                    'lot' => $lot,
                    'current_price' => $price !== null ? (float) $price : null,
                    'market_value' => (float) $marketValue,
                    'pnl' => (float) ($marketValue - $balance),
                ];
            })->values();

        $namedEmitenTotal = $balances->sum() - ($balances['Unspecified'] ?? 0);
        $totalStockValue = (float) $stockPortfolios->sum('balance');
        $tradingBalance = $totalStockValue - $namedEmitenTotal;
        $investedTotal = $namedEmitenTotal;
        $totalPnl = $emitenBalances->sum('pnl');
        $totalEquity = $tradingBalance + $openAmount + $investedTotal + $totalPnl;

        return [
            'stockPortfolios' => $stockPortfolios,
            'totalStockValue' => $totalStockValue,
            'emitenBalances' => $emitenBalances,
            'tradingBalance' => (float) $tradingBalance,
            'openAmount' => (float) $openAmount,
            'investedTotal' => (float) $investedTotal,
            'totalPnl' => (float) $totalPnl,
            'totalEquity' => (float) $totalEquity,
        ];
    }

    public function activityFeed(int $userId): array
    {
        $portfolioIds = $this->financePortfolioRepository->listByInvestmentType($userId, 'stock')->pluck('id')->all();

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

        $data = $invTrx->concat($transferTrx)->sortByDesc('id');

        $ipoTrxIds = $this->financeIpoOrderRepository->queryForUser($userId)
            ->whereIn('finance_investment_id', $portfolioIds)
            ->get(['order_transaction_id', 'release_transaction_id', 'holding_transaction_id', 'offset_transaction_id'])
            ->flatMap(fn ($order) => [$order->order_transaction_id, $order->release_transaction_id, $order->holding_transaction_id, $order->offset_transaction_id])
            ->filter()
            ->values();

        $tradeTrxIds = $this->financeEmitenTradeRepository->linkedTransactionIdsForPortfolios($portfolioIds, $userId);

        return [
            'data' => $data,
            'ipoTrxIds' => $ipoTrxIds,
            'tradeTrxIds' => $tradeTrxIds,
        ];
    }

    public function deleteLedgerEntry(int $userId, int $id): void
    {
        $this->investmentTransactionRepository->ownedOrFail($userId, $id);
        $this->investmentTransactionRepository->delete($id);
    }

    public function updatePrice(int $userId, string $asset, float $price): FinanceEmitenPrice
    {
        return $this->financeEmitenPriceRepository->upsert($userId, $asset, $price);
    }

    public function recordTrade(int $userId, array $data): FinanceEmitenTrade
    {
        $portfolio = $this->financePortfolioService->assertOwnedStockPortfolio($userId, $data['finance_investment_id']);
        $amount = $data['lot'] * 100 * $data['price_per_share'];

        if ($data['type'] === 'buy') {
            return $this->recordBuy($userId, $portfolio->id, $data, $amount);
        }

        return $this->recordSell($userId, $portfolio->id, $data, $amount);
    }

    private function recordBuy(int $userId, int $portfolioId, array $data, float $amount): FinanceEmitenTrade
    {
        $tradingBalance = $this->tradingBalance($portfolioId, $userId);
        if ($amount > $tradingBalance) {
            throw new FinanceDomainException('Trading balance tidak cukup. Tersedia Rp ' . number_format($tradingBalance, 0, ',', '.'));
        }

        return DB::transaction(function () use ($userId, $portfolioId, $data, $amount) {
            $holdingTrx = $this->investmentTransactionRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolioId,
                'asset' => $data['asset'],
                'lot' => $data['lot'],
                'date' => $data['trade_date'],
                'type' => 'deposit',
                'amount' => $amount,
                'description' => 'Buy ' . $data['asset'] . ': ' . $data['lot'] . ' lot @ Rp ' . number_format($data['price_per_share'], 0, ',', '.'),
            ]);

            $cashTrx = $this->investmentTransactionRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolioId,
                'date' => $data['trade_date'],
                'type' => 'withdrawal',
                'amount' => $amount,
                'description' => 'Buy ' . $data['asset'] . ': funded from trading balance',
            ]);

            return $this->financeEmitenTradeRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolioId,
                'asset' => $data['asset'],
                'type' => 'buy',
                'trade_date' => $data['trade_date'],
                'price_per_share' => $data['price_per_share'],
                'lot' => $data['lot'],
                'description' => $data['description'] ?? null,
                'holding_transaction_id' => $holdingTrx->id,
                'cash_transaction_id' => $cashTrx->id,
            ]);
        });
    }

    private function recordSell(int $userId, int $portfolioId, array $data, float $amount): FinanceEmitenTrade
    {
        [$currentLot, $currentInvested] = $this->emitenState($portfolioId, $data['asset'], $userId);

        if ($data['lot'] > $currentLot) {
            throw new FinanceDomainException('Lot tidak cukup. Anda hanya punya ' . $currentLot . ' lot ' . $data['asset']);
        }

        $costOfGoodsSold = $currentLot > 0 ? ($currentInvested * $data['lot'] / $currentLot) : 0;
        $realizedPnl = $amount - $costOfGoodsSold;

        return DB::transaction(function () use ($userId, $portfolioId, $data, $costOfGoodsSold, $realizedPnl) {
            $holdingTrx = $this->investmentTransactionRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolioId,
                'asset' => $data['asset'],
                'lot' => $data['lot'],
                'date' => $data['trade_date'],
                'type' => 'withdrawal',
                'amount' => $costOfGoodsSold,
                'description' => 'Sell ' . $data['asset'] . ': ' . $data['lot'] . ' lot @ Rp ' . number_format($data['price_per_share'], 0, ',', '.'),
            ]);

            $cashTrx = $this->investmentTransactionRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolioId,
                'date' => $data['trade_date'],
                'type' => 'deposit',
                'amount' => $costOfGoodsSold,
                'description' => 'Sell ' . $data['asset'] . ': capital returned to trading balance',
            ]);

            $pnlTrxId = null;
            if (abs($realizedPnl) > 0.004) {
                $pnlTrx = $this->investmentTransactionRepository->create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolioId,
                    'date' => $data['trade_date'],
                    'type' => $realizedPnl > 0 ? 'profit' : 'loss',
                    'amount' => abs($realizedPnl),
                    'description' => 'Sell ' . $data['asset'] . ': realized ' . ($realizedPnl > 0 ? 'profit' : 'loss'),
                ]);
                $pnlTrxId = $pnlTrx->id;
            }

            return $this->financeEmitenTradeRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolioId,
                'asset' => $data['asset'],
                'type' => 'sell',
                'trade_date' => $data['trade_date'],
                'price_per_share' => $data['price_per_share'],
                'lot' => $data['lot'],
                'description' => $data['description'] ?? null,
                'holding_transaction_id' => $holdingTrx->id,
                'cash_transaction_id' => $cashTrx->id,
                'pnl_transaction_id' => $pnlTrxId,
            ]);
        });
    }

    public function deleteTrade(int $userId, int $id): void
    {
        $trade = $this->financeEmitenTradeRepository->ownedOrFail($userId, $id);

        DB::transaction(function () use ($trade) {
            $this->investmentTransactionRepository->destroy(array_filter([
                $trade->holding_transaction_id,
                $trade->cash_transaction_id,
                $trade->pnl_transaction_id,
            ]));

            $this->financeEmitenTradeRepository->delete($trade->id);
        });
    }

    private function namedEmitenBalances(int $portfolioId, int $userId): Collection
    {
        $pendingOrderTrxIds = $this->financeIpoOrderRepository->pendingOrdersForPortfolios([$portfolioId], $userId)
            ->pluck('order_transaction_id')->filter()->values()->all();

        $invTrx = $this->investmentTransactionRepository->forPortfoliosExcluding([$portfolioId], $pendingOrderTrxIds, $userId);
        $transferLegs = $this->financeTransactionRepository->transferLegsForPortfolios([$portfolioId], $userId)->whereNotNull('asset');

        return $this->assetBalanceAggregator->balancesByAsset($invTrx, $transferLegs);
    }

    private function tradingBalance(int $portfolioId, int $userId): float
    {
        $portfolio = $this->financePortfolioService->assertOwned($userId, $portfolioId);
        $balances = $this->namedEmitenBalances($portfolioId, $userId);
        $namedEmitenTotal = $balances->sum() - ($balances['Unspecified'] ?? 0);

        return (float) ($portfolio->balance - $namedEmitenTotal);
    }

    /**
     * @return array{0: int|float, 1: float} [lot, invested]
     */
    private function emitenState(int $portfolioId, string $asset, int $userId): array
    {
        $balances = $this->namedEmitenBalances($portfolioId, $userId);
        $invested = $balances[$asset] ?? 0;

        $pendingOrderTrxIds = $this->financeIpoOrderRepository->pendingOrdersForPortfolios([$portfolioId], $userId)
            ->pluck('order_transaction_id')->filter()->values()->all();

        $invTrx = $this->investmentTransactionRepository->forPortfoliosExcluding([$portfolioId], $pendingOrderTrxIds, $userId);
        $transferLegs = $this->financeTransactionRepository->transferLegsForPortfolios([$portfolioId], $userId);
        $lots = $this->assetBalanceAggregator->lotsByAsset($invTrx, $transferLegs);

        return [$lots[$asset] ?? 0, $invested];
    }
}
