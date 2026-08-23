<?php

namespace App\Repositories\FinanceCategory;

use App\Models\FinanceCategory;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceCategoryRepositoryImplement extends Eloquent implements FinanceCategoryRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceCategory $model)
    {
        $this->model = $model;
    }

    public function query(?string $type = null): Builder
    {
        $query = $this->model->newQuery()->orderBy('name');

        if ($type) {
            $query->where('type', $type);
        }

        return $query;
    }

    public function findOrCreateTransferCategory(): FinanceCategory
    {
        return $this->model->firstOrCreate(
            ['name' => 'Internal Transfer', 'type' => 'expense'],
            ['description' => 'System category for internal transfers between accounts']
        );
    }
}
