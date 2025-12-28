<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;
    protected $roleService;

    public function __construct(
        AuthService $authService,
        \App\Services\Role\RoleService $roleService
    ) {
        $this->authService = $authService;
        $this->roleService = $roleService;
    }

    public function index()
    {
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if ($this->authService->login($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showRegistrationForm()
    {
        $roles = $this->roleService->all();
        return view('pages.auth.register', compact('roles'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $data = $request->only('name', 'email', 'password', 'role_id');
        // By default, only administrator role might be auto-approved or manual?
        // Requirement: "need approval from administrator". So is_approved = false.
        $data['is_approved'] = false; 
        
        $this->authService->register($data);

        return redirect()->route('login')->with('success', 'Registration successful! Please wait for administrator approval.');
    }
}
