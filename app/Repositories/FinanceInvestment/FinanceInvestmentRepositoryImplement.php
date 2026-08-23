<?php

namespace App\Repositories\FinanceInvestment;

use App\Models\FinanceInvestment;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceInvestmentRepositoryImplement extends Eloquent implements FinanceInvestmentRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceInvestment $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->model->newQuery()->orderBy('name');
    }
}
