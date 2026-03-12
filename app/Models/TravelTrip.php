<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'destination',
        'start_date',
        'end_date',
        'total_budget',
        'currency',
        'status',
        'cover_image'
    ];

    public function itineraries()
    {
        return $this->hasMany(TravelItinerary::class, 'trip_id');
    }

    public function budgets()
    {
        return $this->hasMany(TravelBudget::class, 'trip_id');
    }

    public function expenses()
    {
        return $this->hasMany(TravelExpense::class, 'trip_id');
    }

    public function images()
    {
        return $this->hasMany(TravelTripImage::class, 'trip_id');
    }

    public function coverImage()
    {
        return $this->hasOne(TravelTripImage::class, 'trip_id')->where('is_cover', true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ambil URL cover image: prioritaskan dari tabel images, fallback ke kolom cover_image lama.
     */
    public function getCoverUrl(string $default = ''): string
    {
        if ($this->coverImage && $this->coverImage->url) {
            return $this->coverImage->url;
        }
        if ($this->cover_image) {
            return $this->cover_image;
        }
        return $default;
    }
}
