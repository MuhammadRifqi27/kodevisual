<?php

namespace App\Services\FinanceSetting;

use App\Repositories\FinanceSetting\FinanceSettingRepository;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Service;

class FinanceSettingServiceImplement extends Service implements FinanceSettingService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(FinanceSettingRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function allForUser(int $userId): Collection
    {
        return $this->mainRepository->allForUser($userId);
    }

    public function saveMany(int $userId, array $keyValues): void
    {
        $this->mainRepository->upsertMany($userId, $keyValues);
    }
}
