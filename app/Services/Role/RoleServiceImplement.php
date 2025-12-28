<?php

namespace App\Services\Role;

use LaravelEasyRepository\Service;
use App\Repositories\Role\RoleRepository;

class RoleServiceImplement extends Service implements RoleService
{

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected $mainRepository;

  public function __construct(RoleRepository $mainRepository)
  {
    $this->mainRepository = $mainRepository;
  }

  // Define your custom methods :)
}
