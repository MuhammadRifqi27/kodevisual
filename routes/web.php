<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailExpensesController;
use App\Http\Controllers\DetailExpensesRifqiController;
use App\Http\Controllers\MasterCategoryController;
use App\Http\Controllers\UserApprovalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth Routes (Guest only)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Protected Routes
Route::middleware(['auth', 'approved'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('financial_summary')->name('financial_summary.')->group(function () {
        Route::get('/detail-expenses-rifqi', [DetailExpensesRifqiController::class, 'index'])->name('detail.expenses');
        Route::get('/detailExpensesDatatable', [DetailExpensesRifqiController::class, 'detailExpensesdatatable'])->name('detailExpenses.datatable.rifqi');
    });

    Route::prefix('master_category')->name('category.')->group(function () {
        Route::get('/master-category', [MasterCategoryController::class, 'index'])->name('index');
        Route::get('/master-category-datatable', [MasterCategoryController::class, 'categoryDatatable'])->name('datatable');
        Route::post('/store', [MasterCategoryController::class, 'store'])->name('store');
        Route::get('/{masterCategoryExpenses}/edit', [MasterCategoryController::class, 'edit'])->name('edit');
        Route::put('/{masterCategoryExpenses}', [MasterCategoryController::class, 'update'])->name('update');
        Route::delete('/{masterCategoryExpenses}', [MasterCategoryController::class, 'destroy'])->name('destroy');
    });

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/user-approval', [UserApprovalController::class, 'index'])->name('user-approval.index');
        Route::get('/user-approval/datatable', [UserApprovalController::class, 'datatable'])->name('user-approval.datatable');
        Route::post('/user-approval/approve/{id}', [UserApprovalController::class, 'approve'])->name('user-approval.approve');
    });

    Route::get('/dashboard', function () {
        return redirect()->route('dashboard'); // Redirect to actual dashboard route
    })->name('dashboard.redirect');
});
