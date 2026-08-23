<?php

namespace App\Services\FinanceIpoOrder;

use App\Models\FinanceIpoOrder;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\BaseService;

interface FinanceIpoOrderService extends BaseService
{
    public function listQuery(int $userId): Builder;

    public function placeOrder(int $userId, array $data): FinanceIpoOrder;

    /**
     * @throws \App\Exceptions\FinanceDomainException if the order already has an allotment result.
     */
    public function updateOrder(int $userId, int $id, array $data): FinanceIpoOrder;

    /**
     * @throws \App\Exceptions\FinanceDomainException on an invalid state transition.
     */
    public function confirmAllotment(int $userId, int $id, array $data): FinanceIpoOrder;

    public function cancelOrder(int $userId, int $id): void;
}
