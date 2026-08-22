<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function roles()
    {
        return $this->hasMany(AppRole::class);
    }

    public function permissions()
    {
        return $this->hasMany(AppPermission::class);
    }
}
