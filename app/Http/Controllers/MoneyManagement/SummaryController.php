<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceTransaction;
use App\Models\FinanceCategory;
use App\Models\FinancePortfolio;
use App\Models\FinanceNetWorthSnapshot;
use App\Models\FinanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        addVendor('amcharts');
        addVendor('amcharts-maps');
        addVendor('amcharts-stock');
        
        $userId = auth()->id();
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Assets / Net Worth
        $portfolios = FinancePortfolio::where('user_id', $userId)->with('investment')->get();
        $totalNetWorth = $portfolios->sum('balance');
        
        // Asset Allocation Data
        $assetAllocation = $portfolios->map(function($p) {
            return [
                'name' => $p->account_name . ' (' . ($p->investment->name ?? 'Other') . ')',
                'value' => (float)$p->balance,
                'color' => '#'.substr(md5($p->account_name), 0, 6)
            ];
        })->values();

        // Net Worth History (Last 6 months)
        $netWorthHistory = FinanceNetWorthSnapshot::where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->take(12)
            ->get()
            ->map(function($s) {
                return [
                    'date' => $s->date,
                    'amount' => (float)$s->amount
                ];
            });

        // ... rest of existing logic ...

        // Fetch payroll start day from settings (default to 25)
        $payrollDay = FinanceSetting::where('user_id', $userId)->where('key', 'payroll_start_day')->first()?->value ?? 25;
        
        // Calculate current cycle start date based on the payroll day
        $prevMonth = (clone $startDate)->subMonth();
        if ($payrollDay === 'last') {
            $cycleStartDate = $prevMonth->endOfMonth()->startOfDay();
        } else {
            $dayToUse = min((int)$payrollDay, $prevMonth->daysInMonth);
            $cycleStartDate = $prevMonth->day($dayToUse)->startOfDay();
        }

        // 1. Rekapitulasi per Kategori (Pengeluaran saja) - Using Cycle Period
        $categorySummary = FinanceTransaction::where('finance_transactions.user_id', $userId)
            ->where('finance_transactions.type', 'expense')
            ->whereBetween('finance_transactions.date', [$cycleStartDate, $endDate])
            ->join('finance_categories', 'finance_transactions.finance_category_id', '=', 'finance_categories.id')
            ->select('finance_categories.name', DB::raw('SUM(finance_transactions.amount) as total'))
            ->groupBy('finance_categories.name')
            ->orderBy('total', 'desc')
            ->get();

        // 2. Total Income vs Expense bulan ini - Using Cycle Period
        $monthlyStats = FinanceTransaction::where('user_id', $userId)
            ->whereBetween('date', [$cycleStartDate, $endDate])
            ->whereIn('type', ['income', 'expense'])
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        $totalIncome = $monthlyStats['income'] ?? 0;
        $totalExpense = $monthlyStats['expense'] ?? 0;
        $netProfit = $totalIncome - $totalExpense;

        // 3. Perbandingan dengan Bulan Lalu (Previous Cycle)
        // Last cycle end is just before the current cycle starts
        $lastCycleEnd = (clone $cycleStartDate)->subSecond();
        
        // Last cycle start is the payroll day of 2 months ago
        $twoMonthsAgo = (clone $startDate)->subMonths(2);
        if ($payrollDay === 'last') {
            $lastCycleStart = $twoMonthsAgo->endOfMonth()->startOfDay();
        } else {
            $dayToUseLast = min((int)$payrollDay, $twoMonthsAgo->daysInMonth);
            $lastCycleStart = $twoMonthsAgo->day($dayToUseLast)->startOfDay();
        }
        
        $lastMonthExpense = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$lastCycleStart, $lastCycleEnd])
            ->sum('amount');

        // 4. Smart Advisor Logic
        $advice = $this->generateAdvice($totalExpense, $lastMonthExpense, $categorySummary, $netProfit);

        return view('pages.money-management.summary.index', compact(
            'categorySummary', 
            'totalIncome', 
            'totalExpense', 
            'netProfit', 
            'advice',
            'month',
            'year',
            'totalNetWorth',
            'assetAllocation',
            'netWorthHistory'
        ));
    }

    public function takeNetWorthSnapshot()
    {
        $userId = auth()->id();
        $totalBalance = FinancePortfolio::where('user_id', $userId)->get()->sum('balance');

        FinanceNetWorthSnapshot::updateOrCreate(
            ['user_id' => $userId, 'date' => date('Y-m-d')],
            ['amount' => $totalBalance]
        );

        return response()->json(['success' => 'Snapshot Net Worth berhasil diambil']);
    }

    private function generateAdvice($currentExpense, $lastExpense, $categorySummary, $netProfit)
    {
        $advices = [];

        // Bandingkan dengan bulan lalu
        if ($lastExpense > 0) {
            $diff = (($currentExpense - $lastExpense) / $lastExpense) * 100;
            if ($diff > 10) {
                $advices[] = "Pengeluaran Anda naik " . round($diff) . "% dibanding bulan lalu. Coba cek kembali detail pengeluaran untuk melihat bagian mana yang bisa dikurangi.";
            } elseif ($diff < -10) {
                $advices[] = "Bagus! Pengeluaran Anda turun " . round(abs($diff)) . "% dibanding bulan lalu. Pertahankan kebiasaan hemat ini.";
            }
        }

        // Cek Profit
        if ($netProfit < 0) {
            $advices[] = "Bulan ini pengeluaran Anda lebih besar dari pendapatan (Defisit Rp " . number_format(abs($netProfit), 0, ',', '.') . "). Segera evaluasi kebutuhan prioritas Anda.";
        }

        // Cek Top Category
        if ($categorySummary->count() > 0) {
            $topCat = $categorySummary->first();
            $percentage = ($topCat->total / ($currentExpense ?: 1)) * 100;
            if ($percentage > 40) {
                $advices[] = "Kategori '{$topCat->name}' memakan budget sebesar " . round($percentage) . "% dari total pengeluaran. Pertimbangkan untuk mencari alternatif yang lebih murah di kategori ini.";
            }
        }

        if (empty($advices)) {
            if ($currentExpense > 0 || $netProfit != 0) {
                $advices[] = "Keuangan Anda bulan ini relatif stabil. Jangan lupa untuk tetap menyisihkan uang untuk tabungan atau investasi.";
            } else {
                $advices[] = "Belum ada data transaksi untuk periode ini. Silakan input transaksi Anda untuk mendapatkan analisa keuangan.";
            }
        }

        return $advices;
    }
}
