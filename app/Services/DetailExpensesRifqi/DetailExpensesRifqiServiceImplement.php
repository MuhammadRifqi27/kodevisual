<?php

namespace App\Services\DetailExpensesRifqi;

use LaravelEasyRepository\Service;
use App\Repositories\DetailExpensesRifqi\DetailExpensesRifqiRepository;
use Illuminate\Support\Facades\Log;

class DetailExpensesRifqiServiceImplement extends Service implements DetailExpensesRifqiService
{

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected $mainRepository;

  public function __construct(DetailExpensesRifqiRepository $mainRepository)
  {
    $this->mainRepository = $mainRepository;
  }

  public function getAllExpenses()
  {
    try {
      return $this->mainRepository->getAllExpenses();
    } catch (\Exception $exception) {
      Log::error($exception);
      return null;
    }
  }

  // Define your custom methods :)
}
