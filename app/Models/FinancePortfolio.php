<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancePortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_investment_id',
        'account_name',
        'account_number',
        'description',
        'account_investment',
    ];

    protected $casts = [
        'account_investment' => 'boolean',
    ];

    protected $appends = ['balance'];

    public function investment()
    {
        return $this->belongsTo(FinanceInvestment::class, 'finance_investment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(FinanceInvestmentTransaction::class, 'finance_investment_id');
    }

    public function generalTransactions()
    {
        return $this->hasMany(FinanceTransaction::class, 'finance_investment_id');
    }

    public function getBalanceAttribute()
    {
        $userId = auth()->id() ?? $this->user_id;
        
        $invTrxIn = $this->transactions()->where('user_id', $userId)->whereIn('type', ['deposit', 'profit'])->sum('amount');
        $invTrxOut = $this->transactions()->where('user_id', $userId)->whereIn('type', ['withdrawal', 'loss'])->sum('amount');
        
        $genTrx = $this->generalTransactions()->where('user_id', $userId)->whereIn('type', ['income', 'expense'])->get();
        $genBalance = $genTrx->sum(function($trx) {
            if ($trx->type === 'expense') return -$trx->amount;
            return $trx->amount;
        });
        
        // Count transfer transactions separately (net zero across all portfolios)
        $transferIn  = $this->generalTransactions()->where('user_id', $userId)->where('type', 'transfer')->where('amount', '>', 0)->sum('amount');
        $transferOut = $this->generalTransactions()->where('user_id', $userId)->where('type', 'transfer')->where('amount', '<', 0)->sum('amount');

        return ($invTrxIn - $invTrxOut) + $genBalance + ($transferIn + $transferOut);
    }
}
