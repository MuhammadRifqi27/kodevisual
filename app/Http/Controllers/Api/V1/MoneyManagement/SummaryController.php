<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceNetWorthSnapshot;
use App\Models\FinancePortfolio;
use App\Models\FinanceSetting;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $portfolios = FinancePortfolio::where('user_id', $userId)->with('investment')->get();
        $totalNetWorth = $portfolios->sum('balance');

        $assetAllocation = $portfolios->map(function ($p) {
            return [
                'name' => $p->account_name . ' (' . ($p->investment->name ?? 'Other') . ')',
                'value' => (float) $p->balance,
            ];
        })->values();

        $netWorthHistory = FinanceNetWorthSnapshot::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->take(12)
            ->get()
            ->sortBy('date')
            ->map(fn ($s) => ['date' => $s->date, 'amount' => (float) $s->amount])
            ->values();

        $payrollDay = FinanceSetting::where('user_id', $userId)->where('key', 'payroll_start_day')->first()?->value ?? 25;

        $prevMonthDate = (clone $startDate)->subMonth();
        $nextMonthDate = (clone $startDate)->addMonth();

        $calculateStartForBase = function ($base) use ($payrollDay) {
            if ($payrollDay === 'last') {
                return (clone $base)->endOfMonth()->startOfDay();
            }

            $dayToUse = min((int) $payrollDay, $base->daysInMonth);
            return (clone $base)->day($dayToUse)->startOfDay();
        };

        if ($payrollDay === 'last' || (int) $payrollDay > 15) {
            $cycleStartDate = $calculateStartForBase($prevMonthDate);
            $nextCycleStartDate = $calculateStartForBase($startDate);
        } else {
            $cycleStartDate = $calculateStartForBase($startDate);
            $nextCycleStartDate = $calculateStartForBase($nextMonthDate);
        }
        $cycleEndDate = (clone $nextCycleStartDate)->subSecond();

        $categorySummary = FinanceTransaction::where('finance_transactions.user_id', $userId)
            ->where('finance_transactions.type', 'expense')
            ->whereBetween('finance_transactions.date', [$cycleStartDate, $cycleEndDate])
            ->join('finance_categories', 'finance_transactions.finance_category_id', '=', 'finance_categories.id')
            ->select('finance_categories.name', DB::raw('SUM(finance_transactions.amount) as total'))
            ->groupBy('finance_categories.name')
            ->orderBy('total', 'desc')
            ->get();

        $monthlyStats = FinanceTransaction::where('user_id', $userId)
            ->whereBetween('date', [$cycleStartDate, $cycleEndDate])
            ->whereIn('type', ['income', 'expense'])
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        $totalIncome = $monthlyStats['income'] ?? 0;
        $totalExpense = $monthlyStats['expense'] ?? 0;
        $netProfit = $totalIncome - $totalExpense;

        $chartData = [];
        $limitDate = Carbon::create(2026, 1, 1)->startOfDay();
        $currentLoopDate = (clone $startDate);

        while ($currentLoopDate >= $limitDate) {
            $loopStartDate = Carbon::createFromDate($currentLoopDate->year, $currentLoopDate->month, 1)->startOfMonth();
            $loopPrevMonthDate = (clone $loopStartDate)->subMonth();
            $loopNextMonthDate = (clone $loopStartDate)->addMonth();

            if ($payrollDay === 'last' || (int) $payrollDay > 15) {
                $cStart = $calculateStartForBase($loopPrevMonthDate);
                $nextCStart = $calculateStartForBase($loopStartDate);
            } else {
                $cStart = $calculateStartForBase($loopStartDate);
                $nextCStart = $calculateStartForBase($loopNextMonthDate);
            }
            $cEnd = (clone $nextCStart)->subSecond();

            $stats = FinanceTransaction::where('user_id', $userId)
                ->whereBetween('date', [$cStart, $cEnd])
                ->whereIn('type', ['income', 'expense'])
                ->select(
                    DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                    DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
                )
                ->first();

            $chartData[] = [
                'date' => $cEnd->format('Y-m-d'),
                'label' => $cEnd->format('M Y'),
                'income' => (float) ($stats->income ?? 0),
                'expense' => (float) ($stats->expense ?? 0),
            ];

            $currentLoopDate->subMonth();
            if (count($chartData) >= 24) break;
        }
        $chartData = array_reverse($chartData);

        if ($payrollDay === 'last' || (int) $payrollDay > 15) {
            $lastCycleStart = $calculateStartForBase((clone $prevMonthDate)->subMonth());
        } else {
            $lastCycleStart = $calculateStartForBase($prevMonthDate);
        }
        $lastCycleEnd = (clone $cycleStartDate)->subSecond();

        $lastMonthExpense = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$lastCycleStart, $lastCycleEnd])
            ->sum('amount');

        $advice = $this->generateAdvice($totalExpense, $lastMonthExpense, $categorySummary, $netProfit);

        return response()->json([
            'month' => (int) $month,
            'year' => (int) $year,
            'total_net_worth' => (float) $totalNetWorth,
            'asset_allocation' => $assetAllocation,
            'net_worth_history' => $netWorthHistory,
            'category_summary' => $categorySummary,
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'net_profit' => (float) $netProfit,
            'chart_data' => $chartData,
            'advice' => $advice,
        ]);
    }

    public function takeNetWorthSnapshot()
    {
        $userId = auth()->id();
        $totalBalance = FinancePortfolio::where('user_id', $userId)->get()->sum('balance');

        $snapshot = FinanceNetWorthSnapshot::updateOrCreate(
            ['user_id' => $userId, 'date' => date('Y-m-d')],
            ['amount' => $totalBalance]
        );

        return response()->json($snapshot);
    }

    private function generateAdvice($currentExpense, $lastExpense, $categorySummary, $netProfit)
    {
        $advices = [];

        if ($lastExpense > 0) {
            $diff = (($currentExpense - $lastExpense) / $lastExpense) * 100;
            if ($diff > 10) {
                $advices[] = 'Pengeluaran Anda naik ' . round($diff) . '% dibanding bulan lalu. Coba cek kembali detail pengeluaran untuk melihat bagian mana yang bisa dikurangi.';
            } elseif ($diff < -10) {
                $advices[] = 'Bagus! Pengeluaran Anda turun ' . round(abs($diff)) . '% dibanding bulan lalu. Pertahankan kebiasaan hemat ini.';
            }
        }

        if ($netProfit < 0) {
            $advices[] = 'Bulan ini pengeluaran Anda lebih besar dari pendapatan (Defisit Rp ' . number_format(abs($netProfit), 0, ',', '.') . '). Segera evaluasi kebutuhan prioritas Anda.';
        }

        if ($categorySummary->count() > 0) {
            $topCat = $categorySummary->first();
            $percentage = ($topCat->total / ($currentExpense ?: 1)) * 100;
            if ($percentage > 40) {
                $advices[] = "Kategori '{$topCat->name}' memakan budget sebesar " . round($percentage) . '% dari total pengeluaran. Pertimbangkan untuk mencari alternatif yang lebih murah di kategori ini.';
            }
        }

        if (empty($advices)) {
            if ($currentExpense > 0 || $netProfit != 0) {
                $advices[] = 'Keuangan Anda bulan ini relatif stabil. Jangan lupa untuk tetap menyisihkan uang untuk tabungan atau investasi.';
            } else {
                $advices[] = 'Belum ada data transaksi untuk periode ini. Silakan input transaksi Anda untuk mendapatkan analisa keuangan.';
            }
        }

        return $advices;
    }
}
