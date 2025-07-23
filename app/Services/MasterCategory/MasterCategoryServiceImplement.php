<?php

namespace App\Services\MasterCategory;

use LaravelEasyRepository\Service;
use App\Repositories\MasterCategory\MasterCategoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterCategoryServiceImplement extends Service implements MasterCategoryService
{

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected $mainRepository;

  public function __construct(MasterCategoryRepository $mainRepository)
  {
    $this->mainRepository = $mainRepository;
  }

  // Define your custom methods :)
  public function masterCategoryStore($validated_data, $id = null)
  {
    try {
      DB::beginTransaction();

      // Jika ada ID, maka proses update
      if ($id) {
        $master_categort = $this->mainRepository->find($id);
        if (!$master_categort) {
          throw new \Exception('Data Master Category Tidak Ditemukan!');
        }

        // Update data utama
        $this->mainRepository->update($id, [
          'name_category' => $validated_data['name_category'],
        ]);
      } else {
        // Jika tidak ada ID, maka proses create
        $this->mainRepository->create([
          'name_category' => $validated_data['name_category'],
        ]);
      }

      DB::commit();
      return true;
    } catch (\Exception $exception) {
      DB::rollBack();
      Log::error($exception);
      return false;
    }
  }
}
