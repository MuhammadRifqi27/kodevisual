<?php

namespace App\Services\DetailExpenses;

use LaravelEasyRepository\Service;
use App\Repositories\DetailExpenses\DetailExpensesRepository;

class DetailExpensesServiceImplement extends Service implements DetailExpensesService{

     /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
     protected $mainRepository;

    public function __construct(DetailExpensesRepository $mainRepository)
    {
      $this->mainRepository = $mainRepository;
    }

    // Define your custom methods :)
}
