<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'category',
        'amount',
        'notes'
    ];

    public function trip()
    {
        return $this->belongsTo(TravelTrip::class, 'trip_id');
    }
}
