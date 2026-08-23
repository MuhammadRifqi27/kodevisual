<?php

namespace App\Repositories\FinancePortfolio;

use App\Models\FinancePortfolio;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinancePortfolioRepositoryImplement extends Eloquent implements FinancePortfolioRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinancePortfolio $model)
    {
        $this->model = $model;
    }

    public function ownedOrFail(int $userId, $id): FinancePortfolio
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    public function ownedStockPortfolioOrFail(int $userId, $id): FinancePortfolio
    {
        return $this->model->where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('type', 'stock'))
            ->findOrFail($id);
    }

    public function listWithInvestment(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->with('investment')->get();
    }

    public function listByInvestmentType(int $userId, string $type): Collection
    {
        return $this->model->where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('type', $type))
            ->get();
    }

    public function hasAnyTransactions(int $portfolioId): bool
    {
        $portfolio = $this->model->findOrFail($portfolioId);

        return $portfolio->generalTransactions()->exists() || $portfolio->transactions()->exists();
    }

    public function findByAccountNameLike(int $userId, string $pattern): ?FinancePortfolio
    {
        return $this->model->where('user_id', $userId)
            ->where('account_name', 'like', $pattern)
            ->first();
    }
}
