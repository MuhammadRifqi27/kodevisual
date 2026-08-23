<?php

namespace App\Repositories\FinanceIpoOrder;

use App\Models\FinanceIpoOrder;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceIpoOrderRepositoryImplement extends Eloquent implements FinanceIpoOrderRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceIpoOrder $model)
    {
        $this->model = $model;
    }

    public function ownedOrFail(int $userId, $id): FinanceIpoOrder
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    public function queryForUser(int $userId): Builder
    {
        return $this->model->where('user_id', $userId)
            ->with('portfolio')
            ->orderBy('order_date', 'desc');
    }

    public function pendingOrdersForPortfolios(array $portfolioIds, int $userId): \Illuminate\Support\Collection
    {
        return $this->model->where('user_id', $userId)
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNull('lot_allotted')
            ->get();
    }
}
