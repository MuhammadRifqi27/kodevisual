<?php

namespace App\Services\FinanceTransfer;

use App\Models\FinanceTransaction;
use Illuminate\Database\Eloquent\Builder;

/**
 * Not model-backed by a single entity (a transfer is a pair of FinanceTransaction
 * rows), so this deliberately does not extend LaravelEasyRepository\BaseService.
 */
interface FinanceTransferService
{
    public function listQuery(int $userId): Builder;

    /**
     * @return FinanceTransaction The outbound (negative-amount) leg.
     */
    public function transfer(int $userId, array $data): FinanceTransaction;

    /**
     * @return FinanceTransaction The outbound leg, refreshed.
     */
    public function updateTransfer(int $userId, int $id, array $data): FinanceTransaction;

    public function deleteTransfer(int $userId, int $id): void;

    public function findForReceipt(int $userId, int $id): FinanceTransaction;
}
