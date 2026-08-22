<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeddingPlannerItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'estimated_amount',
        'actual_amount',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
