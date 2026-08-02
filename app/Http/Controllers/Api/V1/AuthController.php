<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue a Sanctum personal access token, scoped per device.
     * Mobile app stores the returned token and sends it as
     * "Authorization: Bearer {token}" on every subsequent request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_approved) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda masih menunggu persetujuan administrator.'],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => 'Berhasil logout']);
    }

    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser(User $user): array
    {
        $moneyManagementPermissions = [];
        $appRole = $user->appRoles()
            ->whereHas('app', fn ($q) => $q->where('code', 'money-management'))
            ->with('role.permissions')
            ->first();

        if ($appRole && $appRole->role) {
            $moneyManagementPermissions = $appRole->role->permissions->pluck('code')->values()->all();
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'money_management_permissions' => $moneyManagementPermissions,
        ];
    }
}
