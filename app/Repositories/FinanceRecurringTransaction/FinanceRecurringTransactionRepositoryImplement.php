<?php

namespace App\Repositories\FinanceRecurringTransaction;

use App\Models\FinanceRecurringTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceRecurringTransactionRepositoryImplement extends Eloquent implements FinanceRecurringTransactionRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceRecurringTransaction $model)
    {
        $this->model = $model;
    }

    public function ownedOrFail(int $userId, $id): FinanceRecurringTransaction
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    public function queryForUser(int $userId): Builder
    {
        return $this->model->where('user_id', $userId)
            ->with(['category', 'portfolio'])
            ->orderBy('next_date');
    }

    public function duePending(int $userId, Carbon $today): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('is_active', true)
            ->where('next_date', '<=', $today)
            ->get();
    }
}
