<?php

namespace App\Repositories\WeddingSavingsTransaction;

use App\Models\WeddingSavingsTransaction;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class WeddingSavingsTransactionRepositoryImplement extends Eloquent implements WeddingSavingsTransactionRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(WeddingSavingsTransaction $model)
    {
        $this->model = $model;
    }

    public function forUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->orderBy('date', 'desc')->get();
    }

    public function ownedOrFail(int $userId, $id): WeddingSavingsTransaction
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }
}
