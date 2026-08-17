<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelPackingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'category',
        'item_name',
        'quantity',
        'notes',
        'is_packed',
    ];

    protected $casts = [
        'is_packed' => 'boolean',
    ];

    public function trip()
    {
        return $this->belongsTo(TravelTrip::class, 'trip_id');
    }
}
