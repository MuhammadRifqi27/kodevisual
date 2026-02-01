<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'type',
        'finance_category_id',
        'finance_investment_id',
        'to_finance_investment_id',
        'amount',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }

    public function investment()
    {
        return $this->belongsTo(FinanceInvestment::class, 'finance_investment_id');
    }

    public function destinationAccount()
    {
        return $this->belongsTo(FinanceInvestment::class, 'to_finance_investment_id');
    }
}
