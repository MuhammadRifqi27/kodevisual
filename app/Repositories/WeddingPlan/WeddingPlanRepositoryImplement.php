<?php

namespace App\Repositories\WeddingPlan;

use App\Models\WeddingPlan;
use LaravelEasyRepository\Implementations\Eloquent;

class WeddingPlanRepositoryImplement extends Eloquent implements WeddingPlanRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(WeddingPlan $model)
    {
        $this->model = $model;
    }

    public function firstOrCreateForUser(int $userId, array $defaults): WeddingPlan
    {
        return $this->model->firstOrCreate(['user_id' => $userId], $defaults);
    }

    public function forUser(int $userId): ?WeddingPlan
    {
        return $this->model->where('user_id', $userId)->first();
    }
}
