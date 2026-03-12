<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelItinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'day_number',
        'date',
        'time',
        'activity',
        'location',
        'notes',
        'cost_estimate'
    ];

    public function trip()
    {
        return $this->belongsTo(TravelTrip::class, 'trip_id');
    }
}
