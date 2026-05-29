<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyPlannerActivity extends Model
{
    use HasFactory;

    protected $table = 'daily_planner_activities';

    protected $fillable = [
        'user_id',
        'activity',
        'status',
        'start_datetime',
        'end_datetime',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
    ];

    /**
     * Get the user that owns the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format start datetime to Indonesian day and date format (e.g., "Senin, 29 Mei 2026").
     */
    public function getFormattedDateAttribute()
    {
        Carbon::setLocale('id');
        return Carbon::parse($this->start_datetime)->translatedFormat('l, d F Y');
    }

    /**
     * Format start time to HH:MM format (e.g., "14:30").
     */
    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->start_datetime)->format('H:i');
    }


    /**
     * Get CSS badge class for status.
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'done':
                return 'badge-light-success';
            case 'in progress':
                return 'badge-light-primary';
            case 'not started':
            default:
                return 'badge-light-warning';
        }
    }

    /**
     * Get solid CSS badge class for status.
     */
    public function getStatusSolidBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'done':
                return 'badge-success';
            case 'in progress':
                return 'badge-primary';
            case 'not started':
            default:
                return 'badge-warning';
        }
    }
}
