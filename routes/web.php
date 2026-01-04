<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailExpensesController;
use App\Http\Controllers\DetailExpensesRifqiController;
use App\Http\Controllers\MasterCategoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
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
    
    Route::middleware(['can:financial_summary'])->prefix('financial_summary')->name('financial_summary.')->group(function () {
        Route::get('/detail-expenses-rifqi', [DetailExpensesRifqiController::class, 'index'])->name('detail.expenses');
        Route::get('/detailExpensesDatatable', [DetailExpensesRifqiController::class, 'detailExpensesdatatable'])->name('detailExpenses.datatable.rifqi');
    });

    // Admin Routes
    Route::middleware(['administrator'])->prefix('administrator')->name('administrator.')->group(function () {
        
        Route::middleware(['can:users'])->group(function () {
            Route::get('/user-approval', [UserApprovalController::class, 'index'])->name('user-approval.index');
            Route::get('/user-approval/datatable', [UserApprovalController::class, 'datatable'])->name('user-approval.datatable');
            Route::post('/user-approval/approve/{id}', [UserApprovalController::class, 'approve'])->name('user-approval.approve');
            
            // User List Routes
            Route::get('/user-list', [UserApprovalController::class, 'listing'])->name('user-approval.listing');
            Route::get('/user-list/datatable', [UserApprovalController::class, 'listingDatatable'])->name('user-approval.listing.datatable');
            Route::delete('/user-approval/destroy/{id}', [UserApprovalController::class, 'destroy'])->name('user-approval.destroy');
        });

        // Permission Management
        Route::middleware(['can:permissions'])->controller(PermissionController::class)->prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::put('/update/{id}', 'update')->name('update');
            Route::delete('/destroy/{id}', 'destroy')->name('destroy');
        });

        // Role Management
        Route::middleware(['can:roles'])->controller(RoleController::class)->prefix('roles')->name('roles.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::put('/update/{id}', 'update')->name('update');
            Route::delete('/destroy/{id}', 'destroy')->name('destroy');
        });

        // Master Data Routes
        Route::middleware(['can:master_category'])->prefix('master_category')->name('category.')->group(function () {
            Route::get('/master-category', [MasterCategoryController::class, 'index'])->name('index');
            Route::get('/master-category-datatable', [MasterCategoryController::class, 'categoryDatatable'])->name('datatable');
            Route::post('/store', [MasterCategoryController::class, 'store'])->name('store');
            Route::get('/{masterCategoryExpenses}/edit', [MasterCategoryController::class, 'edit'])->name('edit');
            Route::put('/{masterCategoryExpenses}', [MasterCategoryController::class, 'update'])->name('update');
            Route::delete('/{masterCategoryExpenses}', [MasterCategoryController::class, 'destroy'])->name('destroy');
        });
    });

    Route::get('/dashboard', function () {
        return redirect()->route('dashboard'); // Redirect to actual dashboard route
    })->name('dashboard.redirect');
});
