<?php

namespace App\Repositories\DetailExpensesRifqi;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\DetailExpensesRifqi;

class DetailExpensesRifqiRepositoryImplement extends Eloquent implements DetailExpensesRifqiRepository
{

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(DetailExpensesRifqi $model)
    {
        $this->model = $model;
    }

    public function getAllExpenses()
    {
        return $this->model->with('masterCategory');
    }

    // Write something awesome :)
}
