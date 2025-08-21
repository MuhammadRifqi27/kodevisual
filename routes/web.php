<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailExpensesController;
use App\Http\Controllers\DetailExpensesRifqiController;
use App\Http\Controllers\MasterCategoryController;
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




Route::get('/dashboard', function () {
    return redirect()->route('example');
})->name('dashboard');
