<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelTripImage extends Model
{
    use HasFactory;

    protected $table = 'travel_trip_images';

    protected $fillable = [
        'trip_id',
        'filename',
        'path',
        'url',
        'is_cover',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    public function trip()
    {
        return $this->belongsTo(TravelTrip::class, 'trip_id');
    }
}
