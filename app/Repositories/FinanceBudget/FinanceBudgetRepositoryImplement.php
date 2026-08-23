<?php

namespace App\Repositories\FinanceBudget;

use App\Models\FinanceBudget;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceBudgetRepositoryImplement extends Eloquent implements FinanceBudgetRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceBudget $model)
    {
        $this->model = $model;
    }

    public function forUserMonthYear(int $userId, int $month, int $year): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('finance_category_id');
    }

    public function upsert(int $userId, int $categoryId, int $month, int $year, float $amount): FinanceBudget
    {
        return $this->model->updateOrCreate(
            [
                'user_id' => $userId,
                'finance_category_id' => $categoryId,
                'month' => $month,
                'year' => $year,
            ],
            ['amount' => $amount]
        );
    }
}
