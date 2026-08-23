<?php

namespace App\Services\FinanceTransfer;

use App\Models\FinanceTransaction;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\FinanceCategory\FinanceCategoryService;
use App\Services\FinancePortfolio\FinancePortfolioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FinanceTransferServiceImplement implements FinanceTransferService
{
    public function __construct(
        private FinanceTransactionRepository $financeTransactionRepository,
        private FinancePortfolioService $financePortfolioService,
        private FinanceCategoryService $financeCategoryService,
    ) {
    }

    public function listQuery(int $userId): Builder
    {
        return $this->financeTransactionRepository->transferLegsQuery($userId);
    }

    public function transfer(int $userId, array $data): FinanceTransaction
    {
        $fromAccount = $this->financePortfolioService->assertOwned($userId, $data['from_account_id'])->load('investment');
        $toAccount = $this->financePortfolioService->assertOwned($userId, $data['to_account_id'])->load('investment');
        $category = $this->financeCategoryService->findOrCreateTransferCategory();
        $lot = $this->normalizeLot($data);

        return DB::transaction(function () use ($userId, $data, $fromAccount, $toAccount, $category, $lot) {
            $from = $this->financeTransactionRepository->create([
                'user_id' => $userId,
                'date' => $data['date'],
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $data['from_account_id'],
                'to_finance_investment_id' => $data['to_account_id'],
                'asset' => $data['asset'] ?? null,
                'lot' => $lot !== null ? -$lot : null,
                'amount' => -$data['amount'],
                'description' => $data['description'] ?? ('Transfer to ' . $toAccount->account_name . ' - ' . $toAccount->investment->name),
            ]);

            $this->financeTransactionRepository->create([
                'user_id' => $userId,
                'date' => $data['date'],
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $data['to_account_id'],
                'to_finance_investment_id' => $data['from_account_id'],
                'asset' => $data['asset'] ?? null,
                'lot' => $lot,
                'amount' => $data['amount'],
                'description' => $data['description'] ?? ('Transfer from ' . $fromAccount->account_name . ' - ' . $fromAccount->investment->name),
            ]);

            return $from;
        });
    }

    public function updateTransfer(int $userId, int $id, array $data): FinanceTransaction
    {
        $this->financePortfolioService->assertOwned($userId, $data['from_account_id']);
        $this->financePortfolioService->assertOwned($userId, $data['to_account_id']);

        $outbound = $this->financeTransactionRepository->ownedOrFail($userId, $id);
        $lot = $this->normalizeLot($data);

        DB::transaction(function () use ($outbound, $data, $lot) {
            // FRAGILE: no persisted FK links the two transfer legs; matched heuristically
            // by date + type + swapped accounts + negated amount (pre-existing design,
            // not introduced by this refactor — see docs/money-management-refactor.md).
            $inbound = $this->financeTransactionRepository->findTransferCounterpart($outbound);

            $this->financeTransactionRepository->update($outbound->id, [
                'date' => $data['date'],
                'finance_investment_id' => $data['from_account_id'],
                'to_finance_investment_id' => $data['to_account_id'],
                'asset' => $data['asset'] ?? null,
                'lot' => $lot !== null ? -$lot : null,
                'amount' => -$data['amount'],
                'description' => $data['description'] ?? null,
            ]);

            if ($inbound) {
                $this->financeTransactionRepository->update($inbound->id, [
                    'date' => $data['date'],
                    'finance_investment_id' => $data['to_account_id'],
                    'to_finance_investment_id' => $data['from_account_id'],
                    'asset' => $data['asset'] ?? null,
                    'lot' => $lot,
                    'amount' => $data['amount'],
                    'description' => $data['description'] ?? null,
                ]);
            }
        });

        return $this->financeTransactionRepository->find($outbound->id);
    }

    public function deleteTransfer(int $userId, int $id): void
    {
        $outbound = $this->financeTransactionRepository->ownedOrFail($userId, $id);

        DB::transaction(function () use ($outbound) {
            $inbound = $this->financeTransactionRepository->findTransferCounterpart($outbound);

            if ($inbound) {
                $this->financeTransactionRepository->delete($inbound->id);
            }

            $this->financeTransactionRepository->delete($outbound->id);
        });
    }

    public function findForReceipt(int $userId, int $id): FinanceTransaction
    {
        return $this->financeTransactionRepository->ownedOrFail($userId, $id)->load(['portfolio', 'destinationPortfolio']);
    }

    private function normalizeLot(array $data): ?int
    {
        return isset($data['lot']) && $data['lot'] !== null && $data['lot'] !== ''
            ? (int) $data['lot']
            : null;
    }
}
