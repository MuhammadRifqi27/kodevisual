<?php

namespace App\Services\WeddingPlanner;

/**
 * Not model-backed (aggregates WeddingPlan/Item/SavingsTransaction plus a
 * cross-module FinancePortfolio lookup), so this deliberately does not
 * extend LaravelEasyRepository\BaseService.
 */
interface WeddingPlannerService
{
    public function overview(int $userId): array;

    public function updateTarget(int $userId, array $data): void;

    public function addItem(int $userId, array $data): void;

    public function removeItem(int $userId, int $id): void;

    public function addSavings(int $userId, array $data): void;

    public function removeSavings(int $userId, int $id): void;
}
