<?php

namespace App\Repositories\WeddingPlannerItem;

use App\Models\WeddingPlannerItem;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface WeddingPlannerItemRepository extends Repository
{
    public function forUser(int $userId): Collection;

    public function ownedOrFail(int $userId, $id): WeddingPlannerItem;
}
