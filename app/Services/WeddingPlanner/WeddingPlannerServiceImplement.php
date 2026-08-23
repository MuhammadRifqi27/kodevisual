<?php

namespace App\Services\WeddingPlanner;

use App\Repositories\FinancePortfolio\FinancePortfolioRepository;
use App\Repositories\WeddingPlan\WeddingPlanRepository;
use App\Repositories\WeddingPlannerItem\WeddingPlannerItemRepository;
use App\Repositories\WeddingSavingsTransaction\WeddingSavingsTransactionRepository;

class WeddingPlannerServiceImplement implements WeddingPlannerService
{
    private const DEFAULT_TARGET_AMOUNT = 50000000;
    private const DEFAULT_TARGET_YEARS = 3;

    private const DEFAULT_ITEMS = [
        ['name' => 'Venue & Catering', 'percent' => 45],
        ['name' => 'Decoration & Florist', 'percent' => 15],
        ['name' => 'Photo & Videography', 'percent' => 10],
        ['name' => 'Makeup & Attire', 'percent' => 10],
        ['name' => 'Invitation & Souvenir', 'percent' => 5],
        ['name' => 'Emergency Fund', 'percent' => 15],
    ];

    public function __construct(
        private WeddingPlanRepository $weddingPlanRepository,
        private WeddingPlannerItemRepository $weddingPlannerItemRepository,
        private WeddingSavingsTransactionRepository $weddingSavingsTransactionRepository,
        private FinancePortfolioRepository $financePortfolioRepository,
    ) {
    }

    public function overview(int $userId): array
    {
        $plan = $this->weddingPlanRepository->firstOrCreateForUser($userId, [
            'target_amount' => self::DEFAULT_TARGET_AMOUNT,
            'target_years' => self::DEFAULT_TARGET_YEARS,
        ]);

        $items = $this->weddingPlannerItemRepository->forUser($userId);
        $savings = $this->weddingSavingsTransactionRepository->forUser($userId);

        $bniPortfolio = $this->financePortfolioRepository->findByAccountNameLike($userId, '%Bank BNI%');

        if ($items->isEmpty()) {
            $target = $plan->target_amount;
            foreach (self::DEFAULT_ITEMS as $default) {
                $this->weddingPlannerItemRepository->create([
                    'user_id' => $userId,
                    'name' => $default['name'],
                    'estimated_amount' => ($default['percent'] / 100) * $target,
                    'status' => 'planning',
                ]);
            }
            $items = $this->weddingPlannerItemRepository->forUser($userId);
        }

        return [
            'plan' => $plan,
            'items' => $items,
            'savings' => $savings,
            'totalSavedManual' => $savings->sum('amount'),
            'bniBalance' => $bniPortfolio ? $bniPortfolio->balance : 0,
        ];
    }

    public function updateTarget(int $userId, array $data): void
    {
        $plan = $this->weddingPlanRepository->forUser($userId);
        $oldTarget = $plan ? $plan->target_amount : self::DEFAULT_TARGET_AMOUNT;
        $newTarget = (float) $data['target_amount'];
        $factor = $oldTarget > 0 ? ($newTarget / $oldTarget) : 1;

        if ($plan) {
            $this->weddingPlanRepository->update($plan->id, [
                'target_amount' => $newTarget,
                'target_years' => $data['target_years'],
            ]);
        } else {
            $this->weddingPlanRepository->create([
                'user_id' => $userId,
                'target_amount' => $newTarget,
                'target_years' => $data['target_years'],
            ]);
        }

        foreach ($this->weddingPlannerItemRepository->forUser($userId) as $item) {
            $this->weddingPlannerItemRepository->update($item->id, [
                'estimated_amount' => $item->estimated_amount * $factor,
            ]);
        }
    }

    public function addItem(int $userId, array $data): void
    {
        $this->weddingPlannerItemRepository->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'estimated_amount' => $data['estimated_amount'],
            'status' => 'planning',
        ]);
    }

    public function removeItem(int $userId, int $id): void
    {
        $this->weddingPlannerItemRepository->ownedOrFail($userId, $id);
        $this->weddingPlannerItemRepository->delete($id);
    }

    public function addSavings(int $userId, array $data): void
    {
        $this->weddingSavingsTransactionRepository->create([
            'user_id' => $userId,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function removeSavings(int $userId, int $id): void
    {
        $this->weddingSavingsTransactionRepository->ownedOrFail($userId, $id);
        $this->weddingSavingsTransactionRepository->delete($id);
    }
}
