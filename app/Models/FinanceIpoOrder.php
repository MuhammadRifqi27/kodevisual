<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceIpoOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_investment_id',
        'asset',
        'order_date',
        'price_per_share',
        'lot_ordered',
        'lot_allotted',
        'allotment_date',
        'description',
        'order_transaction_id',
        'release_transaction_id',
        'holding_transaction_id',
        'offset_transaction_id',
    ];

    public function portfolio()
    {
        return $this->belongsTo(FinancePortfolio::class, 'finance_investment_id');
    }

    public function orderTransaction()
    {
        return $this->belongsTo(FinanceInvestmentTransaction::class, 'order_transaction_id');
    }

    public function getStatusAttribute()
    {
        if ($this->lot_allotted === null) {
            return 'pending';
        }
        if ($this->lot_allotted == 0) {
            return 'rejected';
        }
        if ($this->lot_allotted < $this->lot_ordered) {
            return 'partial';
        }
        return 'full';
    }

    public function getOrderAmountAttribute()
    {
        return $this->lot_ordered * 100 * $this->price_per_share;
    }

    public function getAllottedAmountAttribute()
    {
        return $this->lot_allotted !== null ? $this->lot_allotted * 100 * $this->price_per_share : null;
    }

    public function getRefundAmountAttribute()
    {
        return $this->lot_allotted !== null ? $this->order_amount - $this->allotted_amount : null;
    }
}
