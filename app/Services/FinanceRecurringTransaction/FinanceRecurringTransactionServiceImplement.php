<?php

namespace App\Services\FinanceRecurringTransaction;

use App\Models\FinanceRecurringTransaction;
use App\Repositories\FinanceRecurringTransaction\FinanceRecurringTransactionRepository;
use App\Repositories\FinanceTransaction\FinanceTransactionRepository;
use App\Services\FinancePortfolio\FinancePortfolioService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use LaravelEasyRepository\Service;

class FinanceRecurringTransactionServiceImplement extends Service implements FinanceRecurringTransactionService
{
    /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
    protected $mainRepository;

    public function __construct(
        FinanceRecurringTransactionRepository $mainRepository,
        private FinanceTransactionRepository $financeTransactionRepository,
        private FinancePortfolioService $financePortfolioService,
    ) {
        $this->mainRepository = $mainRepository;
    }

    public function listQuery(int $userId): Builder
    {
        return $this->mainRepository->queryForUser($userId);
    }

    public function createRecurring(int $userId, array $data): FinanceRecurringTransaction
    {
        $this->financePortfolioService->assertOwned($userId, $data['finance_investment_id']);

        return $this->mainRepository->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'type' => $data['type'],
            'finance_category_id' => $data['finance_category_id'],
            'finance_investment_id' => $data['finance_investment_id'],
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'start_date' => $data['start_date'],
            'next_date' => $data['start_date'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);
    }

    public function deleteRecurring(int $userId, int $id): void
    {
        $this->mainRepository->ownedOrFail($userId, $id);
        $this->mainRepository->delete($id);
    }

    public function processDue(int $userId): int
    {
        $pendings = $this->mainRepository->duePending($userId, Carbon::today());

        foreach ($pendings as $recurring) {
            $this->financeTransactionRepository->create([
                'user_id' => $userId,
                'date' => $recurring->next_date,
                'type' => $recurring->type,
                'finance_category_id' => $recurring->finance_category_id,
                'finance_investment_id' => $recurring->finance_investment_id,
                'amount' => $recurring->amount,
                'description' => '[Auto] ' . $recurring->name . ($recurring->description ? ': ' . $recurring->description : ''),
            ]);

            $next = Carbon::parse($recurring->next_date);
            switch ($recurring->frequency) {
                case 'daily': $next->addDay(); break;
                case 'weekly': $next->addWeek(); break;
                case 'monthly': $next->addMonth(); break;
                case 'yearly': $next->addYear(); break;
            }

            $this->mainRepository->update($recurring->id, ['next_date' => $next]);
        }

        return $pendings->count();
    }
}
