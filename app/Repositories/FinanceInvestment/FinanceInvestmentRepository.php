<?php

namespace App\Repositories\FinanceInvestment;

use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Repository;

interface FinanceInvestmentRepository extends Repository
{
    public function query(): Builder;
}
