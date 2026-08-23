<?php

namespace App\Repositories\FinanceCategory;

use App\Models\FinanceCategory;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Repository;

interface FinanceCategoryRepository extends Repository
{
    public function query(?string $type = null): Builder;

    public function findOrCreateTransferCategory(): FinanceCategory;
}
