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
        'description',
    ];


    public function transactions()
    {
        return $this->hasMany(FinanceInvestmentTransaction::class, 'finance_investment_id');
    }

    public function generalTransactions()
    {
        return $this->hasMany(FinanceTransaction::class, 'finance_investment_id');
    }
}
