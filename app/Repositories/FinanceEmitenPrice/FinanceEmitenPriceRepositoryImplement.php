<?php

namespace App\Repositories\FinanceEmitenPrice;

use App\Models\FinanceEmitenPrice;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceEmitenPriceRepositoryImplement extends Eloquent implements FinanceEmitenPriceRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceEmitenPrice $model)
    {
        $this->model = $model;
    }

    public function pricesForUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->pluck('current_price', 'asset');
    }

    public function upsert(int $userId, string $asset, float $price): FinanceEmitenPrice
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId, 'asset' => $asset],
            ['current_price' => $price]
        );
    }
}
