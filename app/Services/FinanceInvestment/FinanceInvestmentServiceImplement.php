<?php

namespace App\Services\FinanceInvestment;

use App\Repositories\FinanceInvestment\FinanceInvestmentRepository;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Service;

class FinanceInvestmentServiceImplement extends Service implements FinanceInvestmentService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(FinanceInvestmentRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function query(): Builder
    {
        return $this->mainRepository->query();
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
