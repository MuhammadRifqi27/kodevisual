# Refactor Money Management: Repository + Service Layer

Status: ✅ **selesai — semua 8 batch (termasuk Wedding Planner opsional)**. Standar umumnya ada di [repository-service-pattern.md](repository-service-pattern.md). `php artisan test` hijau (25 test, 760 assertion; satu-satunya kegagalan adalah `Tests\Feature\ExampleTest` pre-existing yang tidak terkait — root `/` redirect 302 bukan 200).

Modul Money Management dipilih sebagai referensi penuh pertama karena paling banyak controller-nya (9 web controller di `MoneyManagement/`, 3 lagi di luar namespace itu — Btc/Stock Tracking, Wedding Planner — plus 13 controller API `Api/V1/MoneyManagement/`), dan controller web & API-nya ternyata **menduplikasi logika bisnis yang sama persis**, cuma beda bentuk response.

## Bug yang ditemukan & diperbaiki selama ekstraksi

Ekstraksi ke Service memaksa memilih satu perilaku kanonik ketika web dan API berbeda. Tiga perbedaan berikut dikonfirmasi sebagai bug (bukan by-design), diperbaiki saat diekstrak, dan masing-masing dapat test regresi:

1. **Konvensi tanda (sign) transaksi recurring.** `FinancePortfolio::getBalanceAttribute()` (satu-satunya "sumber kebenaran" yang sudah dipakai kode lain) mengasumsikan `amount` selalu disimpan positif, tandanya ditentukan dari `type` saat dibaca. `RecurringTransactionController::process()` versi API sudah benar (ikut konvensi ini). Versi web (`processPending()`) menyimpan `amount` yang sudah dinegasi untuk expense — kalau dibaca lewat `getBalanceAttribute()`, tandanya kebalik dua kali. **Fix:** `RecurringTransactionService::processDue()` selalu simpan `amount` positif (ikut konvensi API/Model).
2. **Whitelist ID akun investasi di Dashboard.** Web pakai `[1,2,8,9,11]`, API pakai `[1,2,8,9]`. Dicek langsung ke DB: id 11 = "AJAIB" (platform investasi saham beneran, sekelas id 1/8/9), API kelewatan memasukkannya. **Fix:** disatukan jadi `[1,2,8,9,11]` lewat `config/finance.php` (`investment_account_ids`), dengan catatan ini pendekatan hardcoded-ID yang rapuh — idealnya jadi kolom `is_investment_account` di masa depan (di luar scope refactor ini).
3. **Validasi ledger Portfolio di web lebih longgar dari API.** `storeTransaction`/`updateTransaction` versi web tidak ada guard kepemilikan portfolio dan tidak membatasi `type` ke enum (`deposit|withdrawal|profit|loss`) seperti API. **Fix:** `PortfolioService::createLedgerEntry`/`updateLedgerEntry` pakai aturan API (lebih ketat) untuk kedua caller — ini cuma menolak input yang sebelumnya sudah salah, tidak mengubah data valid yang sudah tersimpan.

## Repository

| Repository | Model | Status |
|---|---|---|
| `FinanceSettingRepository` | `FinanceSetting` | ✅ Batch 0 |
| `FinanceTransactionRepository` | `FinanceTransaction` | ✅ Batch 3 (lengkap: `filteredQuery`, `transferLegsQuery`, `findTransferCounterpart`) |
| `FinancePortfolioRepository` | `FinancePortfolio` | ✅ Batch 2 |
| `FinanceInvestmentTransactionRepository` | `FinanceInvestmentTransaction` | ✅ Batch 2 |
| `FinanceCategoryRepository` | `FinanceCategory` | ✅ Batch 1 |
| `FinanceInvestmentRepository` | `FinanceInvestment` | ✅ Batch 1 |
| `FinanceBudgetRepository` | `FinanceBudget` | ✅ Batch 4 |
| `FinanceRecurringTransactionRepository` | `FinanceRecurringTransaction` | ✅ Batch 4 |
| `FinanceIpoOrderRepository` | `FinanceIpoOrder` | ✅ Batch 5 |
| `FinanceEmitenTradeRepository` | `FinanceEmitenTrade` | ✅ Batch 7 |
| `FinanceNetWorthSnapshotRepository` | `FinanceNetWorthSnapshot` | ✅ Batch 6 |
| `FinanceEmitenPriceRepository` | `FinanceEmitenPrice` | ✅ Batch 7 |
| Wedding* (3 repository) | `WeddingPlan`, `WeddingPlannerItem`, `WeddingSavingsTransaction` | ✅ Batch 8 |

## Service

| Service | Tanggung jawab | Status |
|---|---|---|
| `FinanceCycleService` | Kalkulator tanggal siklus payroll (pure function) | ✅ Batch 0 |
| `FinancialAdviceService` | Generator saran finansial (pure function) | ✅ Batch 0 |
| `FinanceSettingService` | CRUD key/value setting per user | ✅ Batch 0 |
| `App\Services\Support\AssetBalanceAggregator` | Agregasi saldo per-asset (Btc/Stock) — plain class | ✅ Batch 0 |
| `FinanceCategoryService`, `FinanceInvestmentService` | CRUD master data | ✅ Batch 1 |
| `FinancePortfolioService` | Ledger investasi, guard kepemilikan (dipakai Service lain) | ✅ Batch 2 |
| `FinanceTransactionService`, `FinanceTransferService` | Transaksi umum, transfer antar akun | ✅ Batch 3 |
| `FinanceBudgetService`, `FinanceRecurringTransactionService` | Budget, auto-generate transaksi berulang | ✅ Batch 4 |
| `FinanceIpoOrderService` | Order IPO + penjatahan | ✅ Batch 5 |
| `FinanceDashboardService`, `FinanceSummaryService` | Ringkasan keuangan, net worth, saran | ✅ Batch 6 |
| `FinanceBtcTrackingService`, `FinanceStockTrackingService` | Tracking aset crypto/saham, trade | ✅ Batch 7 |
| `WeddingPlannerService` | Plan + items + savings + BNI portfolio lookup, satu service untuk satu controller | ✅ Batch 8 |

## Urutan batch

Tiap batch adalah *vertical slice* kecil — di setiap checkpoint aplikasi tetap jalan penuh, tidak ada yang setengah-migrasi.

0. **Fondasi** — `RepositoryServiceProvider`, `FinanceSettingService`, `FinanceCycleService`, `FinancialAdviceService`, `AssetBalanceAggregator`. ✅ selesai — `php artisan test` hijau (termasuk `FinanceCycleServiceTest` baru yang dicocokkan byte-for-byte ke `PayrollCycleTest`).
1. ✅ CRUD sederhana (Category/Investment) → `MasterDataController` (web) + `CategoryController`/`InvestmentController`/`SettingController` (API). Setting API/web juga dipindah ke `FinanceSettingService` (sudah ada dari Batch 0).
2. ✅ Portfolio (fondasi untuk batch selanjutnya) → `PortfolioController` web+API, termasuk bug fix #3 (ada regression test: `test_portfolio_ledger_rejects_invalid_type_on_both_web_and_api`).
3. ✅ Transaction + Transfer → `TransactionController`, `TransferController` web+API. Heuristic transfer-pair matching disentralkan ke `FinanceTransferService` (masih rapuh — tidak ada FK, cuma tidak lagi terduplikasi 2x).
4. ✅ Budget + Recurring → termasuk bug fix #1 (ada regression test: `test_recurring_processing_always_stores_positive_amount`).
5. ✅ IPO → `IpoController` web+API. `FinancePortfolioRepository::ownedStockPortfolioOrFail` ditambahkan (dipakai lagi di Batch 7 untuk Stock Tracking). Ada test lifecycle end-to-end: `test_ipo_order_lifecycle_creates_and_cleans_up_ledger_entries`.
6. ✅ Dashboard + Summary → termasuk **bug fix #2** via `config/finance.php`. Test: `test_dashboard_classifies_ajaib_as_investment_on_both_web_and_api`. **Catatan implementasi**: ekstraksi `chartData` di `SummaryController` sempat merusak halaman web Summary (500 error, "Call to a member function isEmpty() on array") karena Service awalnya mengembalikan `array_reverse()` biasa (cocok untuk API JSON) padahal Blade view-nya butuh Collection. Fix: `collect(array_reverse($chartData))` — aman untuk kedua caller. Ditemukan lewat root-cause `$response->render()` langsung via tinker, bukan cuma memanggil Service-nya (yang tidak akan menangkap bug render Blade).
7. ✅ Btc/Stock Tracking — paling besar & berisiko (`StockTrackingController` web tadinya 506 baris, agregasi logic terduplikasi 4+x di file itu sendiri, sekarang semua lewat `AssetBalanceAggregator::balancesByAsset/lotsByAsset` satu tempat). Ada test lifecycle end-to-end: `test_stock_trade_lifecycle_creates_and_cleans_up_ledger_entries` (buy → sell dengan weighted-average cost basis → delete, verifikasi P&L ter-booking & ledger bersih).
8. ✅ Wedding Planner (opsional, web-only, tidak ada duplikasi web/API untuk dihilangkan — satu `WeddingPlannerService` menaungi 3 model karena memang cuma satu controller/kapabilitas bisnis).

## Ringkasan akhir

Total yang dibuat: 15 Repository pair (satu per Model), 16 Service pair — 8 di antaranya bukan model tunggal/aggregator lintas-model (`FinanceCycleService`, `FinancialAdviceService`, `FinanceDashboardService`, `FinanceSummaryService`, `FinanceTransferService`, `FinanceBtcTrackingService`, `FinanceStockTrackingService`, `WeddingPlannerService`), 1 provider (`RepositoryServiceProvider`), 1 exception class (`FinanceDomainException`), 1 config file (`config/finance.php`), 1 support class (`AssetBalanceAggregator`). Semua 25 web+API controller di modul ini sekarang lewat Service, tidak ada lagi query Eloquent langsung di controller. 16 test regresi baru ditambahkan ke `tests/Feature/MoneyManagementPagesTest.php`, plus 1 test unit baru (`FinanceCycleServiceTest`).

## Verifikasi tiap batch

- `php artisan test` harus tetap hijau (termasuk `MoneyManagementPagesTest`, `PayrollCycleTest`).
- Smoke test halaman/endpoint yang disentuh batch tsb.
- Untuk batch yang menyentuh data uang (3-7): diff angka hasil hitung sebelum/sesudah — bedanya harus bisa ditelusuri ke salah satu dari 3 bug fix di atas, tidak boleh ada beda lain yang tidak terjelaskan.
