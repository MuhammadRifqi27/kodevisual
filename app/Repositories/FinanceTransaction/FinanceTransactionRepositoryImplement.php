<?php

namespace App\Repositories\FinanceTransaction;

use App\Models\FinanceTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceTransactionRepositoryImplement extends Eloquent implements FinanceTransactionRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceTransaction $model)
    {
        $this->model = $model;
    }

    public function ownedOrFail(int $userId, $id): FinanceTransaction
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    public function forPortfolio(int $portfolioId, int $userId): Collection
    {
        return $this->model->where('finance_investment_id', $portfolioId)
            ->where('user_id', $userId)
            ->with('category')
            ->get();
    }

    public function filteredQuery(int $userId, array $filters = []): Builder
    {
        $query = $this->model->where('user_id', $userId)
            ->whereIn('type', ['income', 'expense'])
            ->with(['category', 'portfolio']);

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('finance_category_id', $filters['category_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query;
    }

    public function transferLegsQuery(int $userId): Builder
    {
        return $this->model->where('user_id', $userId)
            ->where('type', 'transfer')
            ->where('amount', '<', 0)
            ->with(['portfolio', 'destinationPortfolio'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function findTransferCounterpart(FinanceTransaction $leg): ?FinanceTransaction
    {
        return $this->model->where('user_id', $leg->user_id)
            ->where('date', $leg->date)
            ->where('type', 'transfer')
            ->where('finance_investment_id', $leg->to_finance_investment_id)
            ->where('to_finance_investment_id', $leg->finance_investment_id)
            ->where('amount', -$leg->amount)
            ->first();
    }

    public function sumByTypeBetween(int $userId, string $type, $start, $end): float
    {
        return (float) $this->model->where('user_id', $userId)
            ->where('type', $type)
            ->whereBetween('date', [$start, $end])
            ->sum('amount');
    }

    public function groupedByCategoryBetween(int $userId, string $type, $start, $end): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('type', $type)
            ->whereBetween('date', [$start, $end])
            ->select('finance_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('finance_category_id')
            ->get()
            ->keyBy('finance_category_id');
    }

    public function signedBalanceAsOf(int $portfolioId, int $userId, $asOf): float
    {
        return (float) ($this->model->where('finance_investment_id', $portfolioId)
            ->where('user_id', $userId)
            ->where('date', '<=', $asOf)
            ->select(DB::raw("SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as total"))
            ->value('total') ?? 0);
    }

    public function untrackedCashAsOf(int $userId, $asOf): float
    {
        return (float) ($this->model->where('user_id', $userId)
            ->whereNull('finance_investment_id')
            ->where('date', '<=', $asOf)
            ->select(DB::raw("SUM(CASE WHEN type = 'expense' THEN -amount ELSE amount END) as total"))
            ->value('total') ?? 0);
    }

    public function topCategoriesBetween(int $userId, string $type, $start, $end, int $limit, string $fallbackName): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('type', $type)
            ->whereBetween('date', [$start, $end])
            ->with('category')
            ->get()
            ->groupBy('finance_category_id')
            ->map(fn ($group) => [
                'name' => $group->first()->category->name ?? $fallbackName,
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->take($limit)
            ->values();
    }

    public function expenseByCategoryNameBetween(int $userId, $start, $end): Collection
    {
        return $this->model->where('finance_transactions.user_id', $userId)
            ->where('finance_transactions.type', 'expense')
            ->whereBetween('finance_transactions.date', [$start, $end])
            ->join('finance_categories', 'finance_transactions.finance_category_id', '=', 'finance_categories.id')
            ->select('finance_categories.name', DB::raw('SUM(finance_transactions.amount) as total'))
            ->groupBy('finance_categories.name')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function incomeExpenseBetween(int $userId, $start, $end): array
    {
        $stats = $this->model->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->whereIn('type', ['income', 'expense'])
            ->select(
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            )
            ->first();

        return [
            'income' => (float) ($stats->income ?? 0),
            'expense' => (float) ($stats->expense ?? 0),
        ];
    }

    public function recentBetween(int $userId, $start, $end, int $limit): Collection
    {
        return $this->model->where('user_id', $userId)
            ->whereIn('type', ['income', 'expense', 'transfer'])
            ->whereBetween('date', [$start, $end])
            ->with(['category', 'portfolio'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function assetTaggedTransferLegsForPortfolios(array $portfolioIds, int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('type', 'transfer')
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotNull('asset')
            ->with('portfolio')
            ->get();
    }

    public function transferLegsForPortfolios(array $portfolioIds, int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('type', 'transfer')
            ->whereIn('finance_investment_id', $portfolioIds)
            ->get();
    }
}
