<?php

namespace App\Services\Auth;

use LaravelEasyRepository\BaseService;

interface AuthService extends BaseService
{
    public function login($credentials, $remember = false);
    public function register(array $data);
    public function logout();
}
