<?php

namespace App\Repositories\FinanceEmitenPrice;

use App\Models\FinanceEmitenPrice;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface FinanceEmitenPriceRepository extends Repository
{
    /**
     * @return Collection Keyed by asset, value is current_price.
     */
    public function pricesForUser(int $userId): Collection;

    public function upsert(int $userId, string $asset, float $price): FinanceEmitenPrice;
}
