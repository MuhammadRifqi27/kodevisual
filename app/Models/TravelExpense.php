<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'category',
        'description',
        'amount',
        'date',
        'is_pre_trip'
    ];

    public function trip()
    {
        return $this->belongsTo(TravelTrip::class, 'trip_id');
    }
}
