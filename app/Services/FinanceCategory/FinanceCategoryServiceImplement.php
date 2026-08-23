<?php

namespace App\Services\FinanceCategory;

use App\Models\FinanceCategory;
use App\Repositories\FinanceCategory\FinanceCategoryRepository;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Service;

class FinanceCategoryServiceImplement extends Service implements FinanceCategoryService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(FinanceCategoryRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function query(?string $type = null): Builder
    {
        return $this->mainRepository->query($type);
    }

    public function findOrCreateTransferCategory(): FinanceCategory
    {
        return $this->mainRepository->findOrCreateTransferCategory();
    }

    /**
     * Overridden because LaravelEasyRepository\Service::create() does not
     * return the created model, but callers (API responses) need it back.
     */
    public function create($data)
    {
        return $this->mainRepository->create($data);
    }

    /**
     * Overridden because LaravelEasyRepository\Service::update() only
     * returns a bool, but callers (API responses) need the updated model.
     */
    public function update($id, array $data)
    {
        $this->mainRepository->update($id, $data);

        return $this->mainRepository->find($id);
    }
}
