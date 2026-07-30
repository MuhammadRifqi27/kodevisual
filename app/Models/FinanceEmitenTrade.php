<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceEmitenTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_investment_id',
        'asset',
        'type',
        'trade_date',
        'price_per_share',
        'lot',
        'description',
        'holding_transaction_id',
        'cash_transaction_id',
        'pnl_transaction_id',
    ];

    public function portfolio()
    {
        return $this->belongsTo(FinancePortfolio::class, 'finance_investment_id');
    }

    public function getAmountAttribute()
    {
        return $this->lot * 100 * $this->price_per_share;
    }
}
