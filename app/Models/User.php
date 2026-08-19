<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'is_approved',
        'avatar',
    ];

    public function apps()
    {
        return $this->belongsToMany(App::class, 'user_app_roles')
                    ->withPivot('app_role_id');
    }

    public function appRoles()
    {
        return $this->hasMany(UserAppRole::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission($permissionName)
    {
        // Check for app-specific permission format: app_code.permission_code
        if (str_contains($permissionName, '.')) {
            [$appCode, $permissionCode] = explode('.', $permissionName, 2);
            return $this->hasAppPermission($appCode, $permissionCode);
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->permissions->contains('name', $permissionName);
    }

    public function hasAppPermission($appCode, $permissionCode)
    {
        $appRole = $this->appRoles()
            ->whereHas('app', function($q) use ($appCode) {
                $q->where('code', $appCode);
            })
            ->with('role.permissions')
            ->first();

        if (!$appRole || !$appRole->role) {
            return false;
        }

        return $appRole->role->permissions->contains('code', $permissionCode);
    }

    public function financeTransactions()
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function investmentTransactions()
    {
        return $this->hasMany(FinanceInvestmentTransaction::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return null;
    }
}
