<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MoneyManagement\BtcTrackingController;
use App\Http\Controllers\Api\V1\MoneyManagement\BudgetController;
use App\Http\Controllers\Api\V1\MoneyManagement\CategoryController;
use App\Http\Controllers\Api\V1\MoneyManagement\DashboardController;
use App\Http\Controllers\Api\V1\MoneyManagement\InvestmentController;
use App\Http\Controllers\Api\V1\MoneyManagement\IpoController;
use App\Http\Controllers\Api\V1\MoneyManagement\PortfolioController;
use App\Http\Controllers\Api\V1\MoneyManagement\RecurringTransactionController;
use App\Http\Controllers\Api\V1\MoneyManagement\SettingController;
use App\Http\Controllers\Api\V1\MoneyManagement\StockTrackingController;
use App\Http\Controllers\Api\V1\MoneyManagement\SummaryController;
use App\Http\Controllers\Api\V1\MoneyManagement\TransactionController;
use App\Http\Controllers\Api\V1\MoneyManagement\TransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route di sini berjalan di bawah middleware group "api" (stateless,
| bukan session). Auth pakai Laravel Sanctum Personal Access Token, dikirim
| lewat header: Authorization: Bearer {token}.
|
| Permission (gate) yang dipakai mengikuti kode permission yang benar-benar
| ada di tabel app_permissions untuk app "money-management" saat ini:
| dashboard, transactions, budgets, summary, portfolio, settings,
| internal-transfers, recurring, btc-tracking.
| (Catatan: file database/seeders/AppStructureSeeder.php sudah tidak
| lengkap/out-of-date dibanding isi tabel yang sebenarnya - permission
| internal-transfers/recurring/btc-tracking ditambahkan belakangan lewat
| halaman admin, bukan lewat seeder itu. Kalau nanti reset DB dari seeder
| itu saja, permission-permission ini akan hilang lagi.)
|
| IPO & Stock Tracking belum punya kode permission sendiri di web app,
| jadi untuk sementara digabung ke bawah gate "portfolio" (paling dekat
| secara fitur). Kategori/Investasi Provider/Settings (master data)
| digabung ke bawah gate "settings".
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::middleware(['auth:sanctum', 'api.approved'])->prefix('money-management')->group(function () {

        Route::middleware(['can:money-management.dashboard'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index']);
        });

        // Transactions (income/expense)
        Route::middleware(['can:money-management.transactions'])->group(function () {
            Route::apiResource('transactions', TransactionController::class)->except(['show']);
        });

        // Transfers antar akun
        Route::middleware(['can:money-management.internal-transfers'])->group(function () {
            Route::get('/transfers', [TransferController::class, 'index']);
            Route::post('/transfers', [TransferController::class, 'store']);
            Route::put('/transfers/{id}', [TransferController::class, 'update']);
            Route::delete('/transfers/{id}', [TransferController::class, 'destroy']);
        });

        // Transaksi berulang
        Route::middleware(['can:money-management.recurring'])->group(function () {
            Route::get('/recurring', [RecurringTransactionController::class, 'index']);
            Route::post('/recurring', [RecurringTransactionController::class, 'store']);
            Route::delete('/recurring/{id}', [RecurringTransactionController::class, 'destroy']);
            Route::post('/recurring/process', [RecurringTransactionController::class, 'process']);
        });

        // Budgets
        Route::middleware(['can:money-management.budgets'])->group(function () {
            Route::get('/budgets', [BudgetController::class, 'index']);
            Route::post('/budgets', [BudgetController::class, 'store']);
        });

        // Summary & reporting
        Route::middleware(['can:money-management.summary'])->group(function () {
            Route::get('/summary', [SummaryController::class, 'index']);
            Route::post('/summary/net-worth-snapshot', [SummaryController::class, 'takeNetWorthSnapshot']);
        });

        // Portfolio (akun/wallet) + ledger internal + IPO + Stock Tracking
        Route::middleware(['can:money-management.portfolio'])->group(function () {
            Route::get('/portfolios', [PortfolioController::class, 'index']);
            Route::post('/portfolios', [PortfolioController::class, 'store']);
            Route::get('/portfolios/{id}', [PortfolioController::class, 'show']);
            Route::put('/portfolios/{id}', [PortfolioController::class, 'update']);
            Route::delete('/portfolios/{id}', [PortfolioController::class, 'destroy']);
            Route::get('/portfolios/{id}/transactions', [PortfolioController::class, 'transactions']);
            Route::post('/portfolios/{id}/transactions', [PortfolioController::class, 'storeTransaction']);
            Route::put('/portfolios/{id}/transactions/{trxId}', [PortfolioController::class, 'updateTransaction']);
            Route::delete('/portfolios/{id}/transactions/{trxId}', [PortfolioController::class, 'destroyTransaction']);

            Route::get('/ipo', [IpoController::class, 'index']);
            Route::post('/ipo', [IpoController::class, 'store']);
            Route::put('/ipo/{id}', [IpoController::class, 'update']);
            Route::post('/ipo/{id}/confirm-allotment', [IpoController::class, 'confirmAllotment']);
            Route::delete('/ipo/{id}', [IpoController::class, 'destroy']);

            Route::get('/stock-tracking', [StockTrackingController::class, 'index']);
            Route::post('/stock-tracking/price', [StockTrackingController::class, 'updatePrice']);
            Route::post('/stock-tracking/trade', [StockTrackingController::class, 'storeTrade']);
            Route::delete('/stock-tracking/trade/{id}', [StockTrackingController::class, 'destroyTrade']);
            Route::delete('/stock-tracking/{id}', [StockTrackingController::class, 'destroy']);
        });

        // BTC Tracking
        Route::middleware(['can:money-management.btc-tracking'])->group(function () {
            Route::get('/btc-tracking', [BtcTrackingController::class, 'index']);
            Route::delete('/btc-tracking/{id}', [BtcTrackingController::class, 'destroy']);
        });

        // Master data: kategori, provider investasi, pengaturan
        Route::middleware(['can:money-management.settings'])->group(function () {
            Route::get('/categories', [CategoryController::class, 'index']);
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            Route::get('/investments', [InvestmentController::class, 'index']);
            Route::post('/investments', [InvestmentController::class, 'store']);
            Route::put('/investments/{id}', [InvestmentController::class, 'update']);
            Route::delete('/investments/{id}', [InvestmentController::class, 'destroy']);

            Route::get('/settings', [SettingController::class, 'index']);
            Route::post('/settings', [SettingController::class, 'store']);
        });
    });
});
