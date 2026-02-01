<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use App\Services\Role\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    protected $authService;
    protected $roleService;

    public function __construct(
        AuthService $authService,
        RoleService $roleService
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

    public function showForgotPasswordForm()
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Local dev shortcut: skip email and redirect directly to reset password form
        return redirect()->route('password.reset', [
            'token' => 'local-dev-token',
            'email' => $request->email
        ]);
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('pages.auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Local dev shortcut: Directly update user password
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            event(new PasswordReset($user));

            return redirect()->route('login')->with('status', 'Password has been successfully updated (Local Dev Mode).');
        }

        return back()->withErrors(['email' => 'User not found.']);
    }
}
