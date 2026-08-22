<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyPlannerRecurringActivity extends Model
{
    use HasFactory;

    protected $table = 'daily_planner_recurring_activities';

    protected $fillable = [
        'user_id',
        'activity',
        'frequency',
        'day_of_week',
        'day_of_month',
        'start_date',
        'end_date',
        'start_time',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'day_of_week'  => 'array',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_active'    => 'boolean',
    ];

    /**
     * Get the user that owns the recurring activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the generated daily occurrences.
     */
    public function occurrences()
    {
        return $this->hasMany(DailyPlannerActivity::class, 'recurring_activity_id');
    }

    /**
     * Format frequency to Indonesian.
     */
    public function getFormattedFrequencyAttribute()
    {
        switch ($this->frequency) {
            case 'daily':
                return 'Setiap Hari';
            case 'weekly':
                if (is_array($this->day_of_week) && count($this->day_of_week) > 0) {
                    $translatedDays = array_map(function($day) {
                        return $this->translateDayToIndonesian($day);
                    }, $this->day_of_week);
                    return 'Setiap Minggu (' . implode(', ', $translatedDays) . ')';
                }
                return 'Setiap Minggu';
            case 'monthly':
                return 'Setiap Tanggal ' . $this->day_of_month;
            default:
                return ucfirst($this->frequency);
        }
    }

    /**
     * Helper to translate English day to Indonesian.
     */
    private function translateDayToIndonesian($day)
    {
        $days = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        return $days[$day] ?? $day;
    }

    /**
     * Accessor to get the next 5 upcoming occurrences as Carbon dates.
     */
    public function getNextOccurrencesAttribute()
    {
        if (!$this->is_active) {
            return [];
        }

        $occurrences = [];
        $startDate = Carbon::parse($this->start_date);
        
        // Start scanning from today or start_date, whichever is later
        $current = Carbon::today();
        if ($startDate->greaterThan($current)) {
            $current = $startDate->copy();
        }

        $endDate = $this->end_date ? Carbon::parse($this->end_date) : null;
        
        // Loop safety limit (max 365 days scan)
        $limit = 365;
        $dayCounter = 0;

        while (count($occurrences) < 5 && $dayCounter < $limit) {
            if ($endDate && $current->greaterThan($endDate)) {
                break;
            }

            if ($this->matchesDate($current)) {
                $occurrences[] = $current->copy();
            }

            $current->addDay();
            $dayCounter++;
        }

        return $occurrences;
    }

    /**
     * Check if a given date matches the recurrence rule.
     */
    public function matchesDate(Carbon $date)
    {
        $startDate = Carbon::parse($this->start_date);
        if ($date->lessThan($startDate)) {
            return false;
        }

        $endDate = $this->end_date ? Carbon::parse($this->end_date) : null;
        if ($endDate && $date->greaterThan($endDate)) {
            return false;
        }

        switch ($this->frequency) {
            case 'daily':
                return true;

            case 'weekly':
                if (empty($this->day_of_week) || !is_array($this->day_of_week)) {
                    return false;
                }
                // Check if current day name (e.g. "Monday") matches any in user's selected days
                $dayName = $date->format('l');
                return in_array($dayName, $this->day_of_week);

            case 'monthly':
                // Check if date's day of month matches (e.g. 5)
                // If the month has fewer days than day_of_month (e.g. Feb 30th), it won't occur, which is normal.
                return (int)$date->format('d') === (int)$this->day_of_month;

            default:
                return false;
        }
    }
}
