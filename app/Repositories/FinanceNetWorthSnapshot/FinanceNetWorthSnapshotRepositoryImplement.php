<?php

namespace App\Repositories\FinanceNetWorthSnapshot;

use App\Models\FinanceNetWorthSnapshot;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceNetWorthSnapshotRepositoryImplement extends Eloquent implements FinanceNetWorthSnapshotRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceNetWorthSnapshot $model)
    {
        $this->model = $model;
    }

    public function latestForUser(int $userId, int $limit = 12): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->take($limit)
            ->get()
            ->sortBy('date')
            ->values();
    }

    public function upsertForToday(int $userId, string $date, float $amount): FinanceNetWorthSnapshot
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId, 'date' => $date],
            ['amount' => $amount]
        );
    }
}
