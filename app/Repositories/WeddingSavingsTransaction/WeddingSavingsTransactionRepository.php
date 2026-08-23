<?php

namespace App\Repositories\WeddingSavingsTransaction;

use App\Models\WeddingSavingsTransaction;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Repository;

interface WeddingSavingsTransactionRepository extends Repository
{
    public function forUser(int $userId): Collection;

    public function ownedOrFail(int $userId, $id): WeddingSavingsTransaction;
}
