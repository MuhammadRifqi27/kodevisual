<?php

namespace App\Services\FinancialAdvice;

use Illuminate\Support\Collection;

class FinancialAdviceServiceImplement implements FinancialAdviceService
{
    public function generate(float $currentExpense, float $lastExpense, Collection $categorySummary, float $netProfit): array
    {
        $advices = [];

        if ($lastExpense > 0) {
            $diff = (($currentExpense - $lastExpense) / $lastExpense) * 100;
            if ($diff > 10) {
                $advices[] = "Pengeluaran Anda naik " . round($diff) . "% dibanding bulan lalu. Coba cek kembali detail pengeluaran untuk melihat bagian mana yang bisa dikurangi.";
            } elseif ($diff < -10) {
                $advices[] = "Bagus! Pengeluaran Anda turun " . round(abs($diff)) . "% dibanding bulan lalu. Pertahankan kebiasaan hemat ini.";
            }
        }

        if ($netProfit < 0) {
            $advices[] = "Bulan ini pengeluaran Anda lebih besar dari pendapatan (Defisit Rp " . number_format(abs($netProfit), 0, ',', '.') . "). Segera evaluasi kebutuhan prioritas Anda.";
        }

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
