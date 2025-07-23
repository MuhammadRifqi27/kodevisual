<?php

namespace App\Repositories\DetailExpenses;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\DetailExpenses;

class DetailExpensesRepositoryImplement extends Eloquent implements DetailExpensesRepository{

    /**
    * Model class to be used in this repository for the common methods inside Eloquent
    * Don't remove or change $this->model variable name
    * @property Model|mixed $model;
    */
    protected $model;

    public function __construct(DetailExpenses $model)
    {
        $this->model = $model;
    }

    // Write something awesome :)
}
