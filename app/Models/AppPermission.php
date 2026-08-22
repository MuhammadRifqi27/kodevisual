<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPermission extends Model
{
    protected $fillable = ['app_id', 'name', 'code'];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function roles()
    {
        return $this->belongsToMany(AppRole::class, 'app_permission_role');
    }
}
