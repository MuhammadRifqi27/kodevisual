<?php

namespace App\Services\FinanceCategory;

use App\Models\FinanceCategory;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\BaseService;

interface FinanceCategoryService extends BaseService
{
    public function query(?string $type = null): Builder;

    public function findOrCreateTransferCategory(): FinanceCategory;
}
