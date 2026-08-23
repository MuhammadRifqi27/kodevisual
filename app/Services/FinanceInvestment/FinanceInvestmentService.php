<?php

namespace App\Services\FinanceInvestment;

use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\BaseService;

interface FinanceInvestmentService extends BaseService
{
    public function query(): Builder;
}
