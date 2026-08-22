<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceEmitenPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset',
        'current_price',
    ];
}
