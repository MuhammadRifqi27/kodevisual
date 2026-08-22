<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppRole extends Model
{
    protected $fillable = ['app_id', 'name', 'code'];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(AppPermission::class, 'app_permission_role');
    }
}
