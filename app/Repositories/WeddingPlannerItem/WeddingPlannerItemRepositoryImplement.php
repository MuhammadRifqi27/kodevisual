<?php

namespace App\Repositories\WeddingPlannerItem;

use App\Models\WeddingPlannerItem;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class WeddingPlannerItemRepositoryImplement extends Eloquent implements WeddingPlannerItemRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(WeddingPlannerItem $model)
    {
        $this->model = $model;
    }

    public function forUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function ownedOrFail(int $userId, $id): WeddingPlannerItem
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }
}
