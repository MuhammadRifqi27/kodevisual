<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceInvestmentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_investment_id',
        'asset',
        'lot',
        'date',
        'type',
        'amount',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function portfolio()
    {
        return $this->belongsTo(FinancePortfolio::class, 'finance_investment_id');
    }
}
