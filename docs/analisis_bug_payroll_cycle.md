# Dokumen Analisis Teknis: Bug Siklus Payroll & Celah Tanggal (Date Black Hole)

Dokumen ini menjelaskan analisis teknis mengenai masalah tidak terupdatenya dashboard dan halaman Summary Money Management ketika pengguna melakukan transaksi pada tanggal-tanggal tertentu (seperti tanggal 30 Mei) saat opsi **Payroll Start Day** diatur ke **"last" (Last Day of Month)**.

---

## 1. Ringkasan Masalah (Summary)

Ketika pengguna menambahkan transaksi pada **30 Mei 2026**, angka-angka di dashboard keuangan (seperti *Net Worth*, *Income Pool*, *Expense*, dan *Active Balance*) tidak mengalami perubahan. Setelah ditelusuri, transaksi tersebut jatuh ke dalam **"Date Black Hole" (Celah Tanggal)** yang disebabkan oleh kesalahan aritmatika tanggal dalam penentuan batas awal dan akhir siklus keuangan (*financial cycle*).

---

## 2. Analisis Penyebab Utama (Root Cause Analysis)

Sistem menghitung tanggal mulai (`$cycleStartDate`) dan tanggal selesai (`$cycleEndDate`) menggunakan logika berikut di `MoneyManagementDashboardController.php`, `SummaryController.php`, dan `BudgetController.php`:

```php
// Jika payrollDay = 'last'
$cycleStartDate = $baseDate->endOfMonth()->startOfDay(); // Akhir bulan lalu
$cycleEndDate = (clone $cycleStartDate)->addMonth()->subSecond(); // Ditambah 1 bulan, dikurangi 1 detik
```

### Aritmatika Tanggal pada Bulan dengan Jumlah Hari Berbeda (April vs Mei)
Jika hari ini adalah **30 Mei 2026** (Bulan 5) dan `$payrollDay` adalah `"last"`:

1. **Siklus Mei 2026 (Month 5):**
   - `$startDate` ditetapkan ke `2026-05-01`.
   - `$baseDate` dikurangi 1 bulan menjadi `2026-04-01`.
   - `$cycleStartDate` dihitung dari akhir bulan `$baseDate` (April): **`2026-04-30 00:00:00`**.
   - `$cycleEndDate` dihitung dengan menambahkan 1 bulan ke `$cycleStartDate` lalu dikurangi 1 detik:
     - `2026-04-30` + 1 Bulan = **`2026-05-30 00:00:00`** (karena April memiliki 30 hari, Carbon mendaratkan penambahan 1 bulan tepat pada tanggal 30 Mei).
     - Dikurangi 1 detik = **`2026-05-29 23:59:59`**.
   - **Hasil Akhir Siklus Mei:** `2026-04-30 00:00:00` s.d. **`2026-05-29 23:59:59`**.

2. **Siklus Juni 2026 (Month 6):**
   - `$startDate` ditetapkan ke `2026-06-01`.
   - `$baseDate` dikurangi 1 bulan menjadi `2026-05-01`.
   - `$cycleStartDate` dihitung dari akhir bulan `$baseDate` (Mei): **`2026-05-31 00:00:00`**.
   - **Hasil Akhir Siklus Juni:** Dimulai dari **`2026-05-31 00:00:00`**.

### Terbentuknya Celah Tanggal (Date Black Hole)
Dengan perhitungan di atas, mari kita lihat urutan waktunya:
- **Siklus Mei berakhir pada:** `2026-05-29 23:59:59`
- **Siklus Juni dimulai pada:** `2026-05-31 00:00:00`

> [!WARNING]
> **Rentang waktu `2026-05-30 00:00:00` hingga `2026-05-30 23:59:59` (seluruh hari pada tanggal 30 Mei) sepenuhnya hilang dari sistem.**
> Transaksi yang dilakukan pada tanggal 30 Mei tidak termasuk ke dalam siklus Mei (karena melewati batas akhir 29 Mei) dan tidak termasuk ke siklus Juni (karena belum mencapai tanggal 31 Mei).

---

## 3. Dampak Bisnis & Sistem (Impact)

- **Akurasi Data Terganggu:** Transaksi pada tanggal-tanggal celah tidak akan pernah dihitung di dalam grafik pengeluaran, ringkasan saldo bank, maupun kalkulasi *Net Worth* bulanan.
- **Kebingungan Pengguna (UX):** Pengguna merasa aplikasi *error* karena setelah memasukkan transaksi (misalnya input gaji atau pengeluaran harian), saldo dan summary di dashboard tetap tidak berubah.

---

## 4. Solusi Rekomendasi (Recommended Fix)

Untuk menghilangkan celah tanggal (gap-free) untuk **semua jenis pengaturan Payroll Day** (baik angka `1-31` maupun `"last"`), batas akhir siklus (`$cycleEndDate`) tidak boleh dihitung dengan menambahkan 1 bulan secara manual. 

Sebaliknya, **`$cycleEndDate` harus dihitung dari tanggal mulai siklus berikutnya dikurangi 1 detik**.

### Potongan Kode Perbaikan (Recommended Code Replacement)

Ganti blok kalkulasi siklus lama di controller dengan kode berikut:

```php
// 1. Dapatkan tanggal acuan awal bulan saat ini, bulan lalu, dan bulan depan
$startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
$prevMonthDate = (clone $startDate)->subMonth();
$nextMonthDate = (clone $startDate)->addMonth();

// Fungsi helper lokal untuk menghitung tanggal mulai berdasarkan basis bulan
$calculateStartForBase = function($base) use ($payrollDay) {
    if ($payrollDay === 'last') {
        return (clone $base)->endOfMonth()->startOfDay();
    } else {
        $dayToUse = min((int)$payrollDay, $base->daysInMonth);
        return (clone $base)->day($dayToUse)->startOfDay();
    }
};

// 2. Tentukan tanggal mulai siklus saat ini dan siklus berikutnya secara dinamis
if ($payrollDay === 'last' || (int)$payrollDay > 15) {
    // Siklus berawal di bulan sebelumnya (misal gajian tanggal 25)
    $cycleStartDate = $calculateStartForBase($prevMonthDate);
    $nextCycleStartDate = $calculateStartForBase($startDate);
} else {
    // Siklus berawal di bulan berjalan (misal gajian tanggal 5)
    $cycleStartDate = $calculateStartForBase($startDate);
    $nextCycleStartDate = $calculateStartForBase($nextMonthDate);
}

// 3. Batas akhir siklus adalah 1 detik sebelum siklus berikutnya dimulai (Gap-Free!)
$cycleEndDate = (clone $nextCycleStartDate)->subSecond();
```

### Keunggulan Solusi Ini:
1. **100% Bebas Celah (Gap-Free):** Menghubungkan akhir siklus saat ini langsung ke awal siklus berikutnya secara presisi hingga satuan detik.
2. **Konsisten:** Mendukung semua konfigurasi gajian (`last`, tanggal 31 di bulan pendek seperti Februari, dsb.) secara otomatis tanpa *wrapping date bug* dari Carbon.

---
*Dokumen ini dibuat secara otomatis untuk membantu tim pengembang menyelesaikan masalah di masa mendatang.*
