<?php

namespace App\Repositories\WeddingPlan;

use App\Models\WeddingPlan;
use LaravelEasyRepository\Repository;

interface WeddingPlanRepository extends Repository
{
    public function firstOrCreateForUser(int $userId, array $defaults): WeddingPlan;

    public function forUser(int $userId): ?WeddingPlan;
}
