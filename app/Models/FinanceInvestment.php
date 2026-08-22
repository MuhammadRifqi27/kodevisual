<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceInvestment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
    ];

    public function portfolios()
    {
        return $this->hasMany(FinancePortfolio::class, 'finance_investment_id');
    }
}
