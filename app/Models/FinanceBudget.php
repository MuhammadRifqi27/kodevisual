<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_category_id',
        'amount',
        'month',
        'year',
    ];

    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
