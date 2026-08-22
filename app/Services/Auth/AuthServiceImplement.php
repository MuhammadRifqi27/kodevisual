<?php

namespace App\Services\Auth;

use LaravelEasyRepository\Service;
use App\Repositories\Auth\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthServiceImplement extends Service implements AuthService
{

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected $mainRepository;

  public function __construct(AuthRepository $mainRepository)
  {
    $this->mainRepository = $mainRepository;
  }

  public function login($credentials, $remember = false)
  {
      if (Auth::attempt($credentials, $remember)) {
          return true;
      }
      return false;
  }

  public function register(array $data)
  {
      return $this->mainRepository->create([
          'name' => $data['name'],
          'email' => $data['email'],
          'password' => $data['password'],
          'role_id' => $data['role_id'],
          'is_approved' => $data['is_approved'] ?? false,
      ]);
  }

  public function logout()
  {
      Auth::logout();
  }
}
