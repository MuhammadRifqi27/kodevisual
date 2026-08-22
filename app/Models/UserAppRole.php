<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAppRole extends Model
{
    protected $fillable = ['user_id', 'app_id', 'app_role_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function role()
    {
        return $this->belongsTo(AppRole::class, 'app_role_id');
    }
}
