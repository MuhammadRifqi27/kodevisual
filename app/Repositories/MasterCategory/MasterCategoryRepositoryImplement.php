<?php

namespace App\Repositories\MasterCategory;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\MasterCategoryExpenses;

class MasterCategoryRepositoryImplement extends Eloquent implements MasterCategoryRepository
{

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(MasterCategoryExpenses $model)
    {
        $this->model = $model;
    }

    // Write something awesome :)
}
