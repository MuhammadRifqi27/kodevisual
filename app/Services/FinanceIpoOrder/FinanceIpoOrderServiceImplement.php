<?php

namespace App\Services\FinanceIpoOrder;

use App\Exceptions\FinanceDomainException;
use App\Models\FinanceIpoOrder;
use App\Repositories\FinanceIpoOrder\FinanceIpoOrderRepository;
use App\Repositories\FinanceInvestmentTransaction\FinanceInvestmentTransactionRepository;
use App\Services\FinancePortfolio\FinancePortfolioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LaravelEasyRepository\Service;

class FinanceIpoOrderServiceImplement extends Service implements FinanceIpoOrderService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(
        FinanceIpoOrderRepository $mainRepository,
        private FinanceInvestmentTransactionRepository $investmentTransactionRepository,
        private FinancePortfolioService $financePortfolioService,
    ) {
        $this->mainRepository = $mainRepository;
    }

    public function listQuery(int $userId): Builder
    {
        return $this->mainRepository->queryForUser($userId);
    }

    public function placeOrder(int $userId, array $data): FinanceIpoOrder
    {
        $this->financePortfolioService->assertOwnedStockPortfolio($userId, $data['finance_investment_id']);

        $orderAmount = $data['lot_ordered'] * 100 * $data['price_per_share'];

        return DB::transaction(function () use ($userId, $data, $orderAmount) {
            $order = $this->mainRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $data['finance_investment_id'],
                'asset' => $data['asset'],
                'order_date' => $data['order_date'],
                'price_per_share' => $data['price_per_share'],
                'lot_ordered' => $data['lot_ordered'],
                'description' => $data['description'] ?? null,
            ]);

            $orderTrx = $this->investmentTransactionRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $data['finance_investment_id'],
                'asset' => $data['asset'],
                'date' => $data['order_date'],
                'type' => 'withdrawal',
                'amount' => $orderAmount,
                'description' => 'IPO Order: ' . $data['asset'] . ' - ' . $data['lot_ordered'] . ' lot @ Rp ' . number_format($data['price_per_share'], 0, ',', '.') . ' (pending allotment)',
            ]);

            $this->mainRepository->update($order->id, ['order_transaction_id' => $orderTrx->id]);

            return $this->mainRepository->find($order->id);
        });
    }

    public function updateOrder(int $userId, int $id, array $data): FinanceIpoOrder
    {
        $order = $this->mainRepository->ownedOrFail($userId, $id);

        if ($order->status !== 'pending') {
            throw new FinanceDomainException('Pesanan yang sudah ada hasil penjatahan tidak bisa diedit. Hapus lalu buat ulang jika perlu koreksi.');
        }

        $this->financePortfolioService->assertOwnedStockPortfolio($userId, $data['finance_investment_id']);

        $orderAmount = $data['lot_ordered'] * 100 * $data['price_per_share'];

        DB::transaction(function () use ($data, $order, $orderAmount) {
            $this->mainRepository->update($order->id, [
                'finance_investment_id' => $data['finance_investment_id'],
                'asset' => $data['asset'],
                'order_date' => $data['order_date'],
                'price_per_share' => $data['price_per_share'],
                'lot_ordered' => $data['lot_ordered'],
                'description' => $data['description'] ?? null,
            ]);

            $order->orderTransaction()->update([
                'finance_investment_id' => $data['finance_investment_id'],
                'asset' => $data['asset'],
                'date' => $data['order_date'],
                'amount' => $orderAmount,
                'description' => 'IPO Order: ' . $data['asset'] . ' - ' . $data['lot_ordered'] . ' lot @ Rp ' . number_format($data['price_per_share'], 0, ',', '.') . ' (pending allotment)',
            ]);
        });

        return $this->mainRepository->find($order->id);
    }

    public function confirmAllotment(int $userId, int $id, array $data): FinanceIpoOrder
    {
        $order = $this->mainRepository->ownedOrFail($userId, $id);

        if ($order->status !== 'pending') {
            throw new FinanceDomainException('Pesanan ini sudah punya hasil penjatahan');
        }

        if ($data['lot_allotted'] > $order->lot_ordered) {
            throw new FinanceDomainException('Lot allotted tidak boleh lebih besar dari lot yang dipesan');
        }

        $orderAmount = $order->order_amount;
        $allottedAmount = $data['lot_allotted'] * 100 * $order->price_per_share;

        DB::transaction(function () use ($data, $order, $userId, $orderAmount, $allottedAmount) {
            $releaseTrx = $this->investmentTransactionRepository->create([
                'user_id' => $userId,
                'finance_investment_id' => $order->finance_investment_id,
                'asset' => $order->asset,
                'date' => $data['allotment_date'],
                'type' => 'deposit',
                'amount' => $orderAmount,
                'description' => 'IPO Block Released: ' . $order->asset,
            ]);

            $holdingTrxId = null;
            $offsetTrxId = null;

            if ($data['lot_allotted'] > 0) {
                $holdingTrx = $this->investmentTransactionRepository->create([
                    'user_id' => $userId,
                    'finance_investment_id' => $order->finance_investment_id,
                    'asset' => $order->asset,
                    'lot' => $data['lot_allotted'],
                    'date' => $data['allotment_date'],
                    'type' => 'deposit',
                    'amount' => $allottedAmount,
                    'description' => 'IPO Allotment: ' . $order->asset . ' - ' . $data['lot_allotted'] . '/' . $order->lot_ordered . ' lot @ Rp ' . number_format($order->price_per_share, 0, ',', '.'),
                ]);
                $holdingTrxId = $holdingTrx->id;

                $offsetTrx = $this->investmentTransactionRepository->create([
                    'user_id' => $userId,
                    'finance_investment_id' => $order->finance_investment_id,
                    'date' => $data['allotment_date'],
                    'type' => 'withdrawal',
                    'amount' => $allottedAmount,
                    'description' => 'IPO Allotment funded from broker balance: ' . $order->asset,
                ]);
                $offsetTrxId = $offsetTrx->id;
            }

            $this->mainRepository->update($order->id, [
                'lot_allotted' => $data['lot_allotted'],
                'allotment_date' => $data['allotment_date'],
                'release_transaction_id' => $releaseTrx->id,
                'holding_transaction_id' => $holdingTrxId,
                'offset_transaction_id' => $offsetTrxId,
            ]);
        });

        return $this->mainRepository->find($order->id);
    }

    public function cancelOrder(int $userId, int $id): void
    {
        $order = $this->mainRepository->ownedOrFail($userId, $id);

        DB::transaction(function () use ($order) {
            $this->investmentTransactionRepository->destroy(array_filter([
                $order->order_transaction_id,
                $order->release_transaction_id,
                $order->holding_transaction_id,
                $order->offset_transaction_id,
            ]));

            $this->mainRepository->delete($order->id);
        });
    }
}
