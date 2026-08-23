<?php

namespace App\Repositories\FinanceInvestmentTransaction;

use App\Models\FinanceInvestmentTransaction;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceInvestmentTransactionRepositoryImplement extends Eloquent implements FinanceInvestmentTransactionRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceInvestmentTransaction $model)
    {
        $this->model = $model;
    }

    public function ownedOrFail(int $userId, $id): FinanceInvestmentTransaction
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    public function forPortfolio(int $portfolioId, int $userId): Collection
    {
        return $this->model->where('finance_investment_id', $portfolioId)
            ->where('user_id', $userId)
            ->get();
    }

    public function forPortfolios(array $portfolioIds, int $userId): Collection
    {
        return $this->model->whereIn('finance_investment_id', $portfolioIds)
            ->where('user_id', $userId)
            ->with('portfolio')
            ->get();
    }

    public function signedBalanceAsOf(int $portfolioId, int $userId, $asOf): float
    {
        $in = $this->model->where('finance_investment_id', $portfolioId)
            ->where('user_id', $userId)
            ->where('date', '<=', $asOf)
            ->whereIn('type', ['deposit', 'profit'])
            ->sum('amount');

        $out = $this->model->where('finance_investment_id', $portfolioId)
            ->where('user_id', $userId)
            ->where('date', '<=', $asOf)
            ->whereIn('type', ['withdrawal', 'loss'])
            ->sum('amount');

        return (float) ($in - $out);
    }

    public function forPortfoliosExcluding(array $portfolioIds, array $excludeIds, int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotIn('id', $excludeIds)
            ->get();
    }
}
