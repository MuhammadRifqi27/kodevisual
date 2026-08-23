<?php

namespace App\Repositories\FinanceEmitenTrade;

use App\Models\FinanceEmitenTrade;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceEmitenTradeRepositoryImplement extends Eloquent implements FinanceEmitenTradeRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceEmitenTrade $model)
    {
        $this->model = $model;
    }

    public function ownedOrFail(int $userId, $id): FinanceEmitenTrade
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    public function linkedTransactionIdsForPortfolios(array $portfolioIds, int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->whereIn('finance_investment_id', $portfolioIds)
            ->get(['holding_transaction_id', 'cash_transaction_id', 'pnl_transaction_id'])
            ->flatMap(fn ($trade) => [$trade->holding_transaction_id, $trade->cash_transaction_id, $trade->pnl_transaction_id])
            ->filter()
            ->values();
    }
}
