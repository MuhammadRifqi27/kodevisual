<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceRecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'finance_category_id',
        'finance_investment_id',
        'amount',
        'frequency',
        'start_date',
        'next_date',
        'description',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }

    public function portfolio()
    {
        return $this->belongsTo(FinancePortfolio::class, 'finance_investment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
