<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BtcTrackingController;
use App\Http\Controllers\StockTrackingController;
use App\Http\Controllers\DailyPlannerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetailExpensesController;
use App\Http\Controllers\DetailExpensesRifqiController;
use App\Http\Controllers\MasterCategoryController;
use App\Http\Controllers\MoneyManagement\BudgetController;
use App\Http\Controllers\MoneyManagement\IpoController;
use App\Http\Controllers\MoneyManagement\MasterDataController;
use App\Http\Controllers\MoneyManagement\MoneyManagementDashboardController;
use App\Http\Controllers\MoneyManagement\PortfolioController;
use App\Http\Controllers\MoneyManagement\RecurringTransactionController;
use App\Http\Controllers\MoneyManagement\TransferController;
use App\Http\Controllers\MoneyManagement\SummaryController;
use App\Http\Controllers\MoneyManagement\TransactionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Controllers\UserAppController;
use App\Http\Controllers\Travel\TravelDashboardController;
use App\Http\Controllers\Travel\TravelTripController;
use App\Http\Controllers\Travel\TravelItineraryController;
use App\Http\Controllers\Travel\TravelBudgetController;
use App\Http\Controllers\Travel\TravelExpenseController;
use App\Http\Controllers\Travel\TravelPackingItemController;
use App\Http\Controllers\WeddingPlannerController;
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

    // Forgot Password Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Protected Routes
Route::middleware(['auth', 'approved'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['can:financial_summary'])->prefix('financial_summary')->name('financial_summary.')->group(function () {
        Route::get('/detail-expenses-rifqi', [DetailExpensesRifqiController::class, 'index'])->name('detail.expenses');
        Route::get('/detailExpensesDatatable', [DetailExpensesRifqiController::class, 'detailExpensesdatatable'])->name('detailExpenses.datatable.rifqi');
    });


    Route::middleware(['can:travel-planner.dashboard'])->prefix('travel')->name('travel.')->group(function () {
        Route::get('/dashboard', [TravelDashboardController::class, 'index'])->name('dashboard');

        // Trips
        Route::middleware(['can:travel-planner.trips'])->prefix('trips')->name('trips.')->group(function () {
            Route::get('/', [TravelTripController::class, 'index'])->name('index');
            Route::get('/export', [TravelTripController::class, 'exportAll'])->name('export_all');
            Route::get('/create', [TravelTripController::class, 'create'])->name('create');
            Route::post('/', [TravelTripController::class, 'store'])->name('store');
            Route::get('/{id}', [TravelTripController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [TravelTripController::class, 'edit'])->name('edit');
            Route::put('/{id}', [TravelTripController::class, 'update'])->name('update');
            Route::delete('/{id}', [TravelTripController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/export/full', [TravelTripController::class, 'exportFull'])->name('export_full');
            Route::get('/{id}/export/itinerary', [TravelTripController::class, 'exportItinerary'])->name('export_itinerary');
        });

        // Itineraries
        Route::post('/itineraries', [TravelItineraryController::class, 'store'])->name('itineraries.store');
        Route::put('/itineraries/{id}', [TravelItineraryController::class, 'update'])->name('itineraries.update');
        Route::delete('/itineraries/{id}', [TravelItineraryController::class, 'destroy'])->name('itineraries.destroy');

        // Packing List
        Route::post('/packing-items', [TravelPackingItemController::class, 'store'])->name('packing-items.store');
        Route::put('/packing-items/{id}', [TravelPackingItemController::class, 'update'])->name('packing-items.update');
        Route::patch('/packing-items/{id}/toggle', [TravelPackingItemController::class, 'togglePacked'])->name('packing-items.toggle');
        Route::delete('/packing-items/{id}', [TravelPackingItemController::class, 'destroy'])->name('packing-items.destroy');

        // Budgets
        Route::middleware(['can:travel-planner.budgets'])->prefix('budgets')->name('budgets.')->group(function () {
            Route::get('/', [TravelBudgetController::class, 'index'])->name('index');
            Route::post('/', [TravelBudgetController::class, 'store'])->name('store');
            Route::put('/{id}', [TravelBudgetController::class, 'update'])->name('update');
            Route::delete('/{id}', [TravelBudgetController::class, 'destroy'])->name('destroy');
        });

        // Expenses
        Route::middleware(['can:travel-planner.budgets'])->prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [TravelExpenseController::class, 'index'])->name('index');
            Route::post('/', [TravelExpenseController::class, 'store'])->name('store');
            Route::delete('/{id}', [TravelExpenseController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware(['can:money-management.dashboard'])->group(function () {
        Route::prefix('money-management')->name('money-management.')->group(function () {
            Route::get('/dashboard', [MoneyManagementDashboardController::class, 'index'])->name('dashboard');

            // Transactions
            Route::middleware(['can:money-management.transactions'])->prefix('transactions')->name('transactions.')->group(function () {
                Route::get('/', [TransactionController::class, 'index'])->name('index');
                Route::get('/datatable', [TransactionController::class, 'datatable'])->name('datatable');
                Route::get('/categories', [TransactionController::class, 'getCategories'])->name('get-categories');
                Route::post('/', [TransactionController::class, 'store'])->name('store');
                Route::put('/{id}', [TransactionController::class, 'update'])->name('update');
                Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('destroy');
            });

            // Summary & Net Worth
            Route::middleware(['can:money-management.summary'])->prefix('summary')->name('summary.')->group(function () {
                Route::get('/', [SummaryController::class, 'index'])->name('index');
                Route::get('/net-worth-snapshot', [SummaryController::class, 'takeNetWorthSnapshot'])->name('net-worth-snapshot');
            });

            // Budgets
            Route::middleware(['can:money-management.budgets'])->prefix('budgets')->name('budgets.')->group(function () {
                Route::get('/', [BudgetController::class, 'index'])->name('index');
                Route::post('/', [BudgetController::class, 'store'])->name('store');
            });

            // Transfers
            Route::middleware(['can:money-management.internal-transfers'])->prefix('transfers')->name('transfers.')->group(function () {
                Route::get('/', [TransferController::class, 'index'])->name('index');
                Route::get('/datatable', [TransferController::class, 'datatable'])->name('datatable');
                Route::post('/', [TransferController::class, 'store'])->name('store');
                Route::put('/{id}', [TransferController::class, 'update'])->name('update');
                Route::get('/receipt/{id}', [TransferController::class, 'showReceipt'])->name('receipt');
                Route::delete('/{id}', [TransferController::class, 'destroy'])->name('destroy');
            });

            // Recurring Transactions
            Route::middleware(['can:money-management.recurring'])->prefix('recurring')->name('recurring.')->group(function () {
                Route::get('/', [RecurringTransactionController::class, 'index'])->name('index');
                Route::get('/datatable', [RecurringTransactionController::class, 'datatable'])->name('datatable');
                Route::post('/', [RecurringTransactionController::class, 'store'])->name('store');
                Route::delete('/{id}', [RecurringTransactionController::class, 'destroy'])->name('destroy');
            });

            // Portfolio / Savings
            // Portfolio
            Route::group(['prefix' => 'portfolio', 'as' => 'portfolio.'], function () {
                Route::get('/', [PortfolioController::class, 'index'])->name('index');
                Route::get('/datatable', [PortfolioController::class, 'datatable'])->name('datatable');
                Route::post('/store', [PortfolioController::class, 'store'])->name('store');
                Route::put('/{id}', [PortfolioController::class, 'update'])->name('update');
                Route::delete('/{id}', [PortfolioController::class, 'destroy'])->name('destroy');
                Route::get('/{id}', [PortfolioController::class, 'show'])->name('show');

                // Internal Transactions inside portfolio
                Route::get('/{id}/transactions', [PortfolioController::class, 'transactionDatatable'])->name('transactions.datatable');
                Route::post('/transaction/store', [PortfolioController::class, 'storeTransaction'])->name('transactions.store');
                Route::post('/transaction/update/{id}', [PortfolioController::class, 'updateTransaction'])->name('transactions.update');
                Route::delete('/transaction/destroy/{id}', [PortfolioController::class, 'destroyTransaction'])->name('transactions.destroy');
            });

            // Bitcoin Tracking
            Route::group(['prefix' => 'btc-tracking', 'as' => 'btc-tracking.'], function () {
                Route::get('/', [BtcTrackingController::class, 'index'])->name('index');
                Route::get('/datatable', [BtcTrackingController::class, 'datatable'])->name('datatable');
                Route::post('/store', [BtcTrackingController::class, 'store'])->name('store');
                Route::delete('/{id}', [BtcTrackingController::class, 'destroy'])->name('destroy');
            });

            // Stock Tracking
            Route::group(['prefix' => 'stock-tracking', 'as' => 'stock-tracking.'], function () {
                Route::get('/', [StockTrackingController::class, 'index'])->name('index');
                Route::get('/datatable', [StockTrackingController::class, 'datatable'])->name('datatable');
                Route::post('/store', [StockTrackingController::class, 'store'])->name('store');
                Route::post('/price', [StockTrackingController::class, 'updatePrice'])->name('price');
                Route::post('/trade', [StockTrackingController::class, 'storeTrade'])->name('trade.store');
                Route::delete('/trade/{id}', [StockTrackingController::class, 'destroyTrade'])->name('trade.destroy');
                Route::delete('/{id}', [StockTrackingController::class, 'destroy'])->name('destroy');
            });

            // IPO Orders
            Route::prefix('ipo')->name('ipo.')->group(function () {
                Route::get('/', [IpoController::class, 'index'])->name('index');
                Route::get('/datatable', [IpoController::class, 'datatable'])->name('datatable');
                Route::post('/', [IpoController::class, 'store'])->name('store');
                Route::put('/{id}', [IpoController::class, 'update'])->name('update');
                Route::post('/{id}/allotment', [IpoController::class, 'confirmAllotment'])->name('allotment');
                Route::delete('/{id}', [IpoController::class, 'destroy'])->name('destroy');
            });

            // Wedding
            Route::prefix('wedding-planner')->name('wedding-planner.')->group(function () {
                Route::get('/', [WeddingPlannerController::class, 'index'])->name('index');
                Route::post('/', [WeddingPlannerController::class, 'store'])->name('store');
                Route::post('/item', [WeddingPlannerController::class, 'storeItem'])->name('item.store');
                Route::delete('/item/{id}', [WeddingPlannerController::class, 'destroyItem'])->name('item.destroy');

                // Savings Transactions
                Route::post('/savings', [WeddingPlannerController::class, 'storeSavings'])->name('savings.store');
                Route::delete('/savings/{id}', [WeddingPlannerController::class, 'destroySavings'])->name('savings.destroy');
            });

            // Master Data Routes
            Route::prefix('master-data')->name('master-data.')->group(function () {
                // Category Expenses
                Route::get('/expenses', [MasterDataController::class, 'expensesIndex'])->name('expenses.index');
                Route::get('/expenses/datatable', [MasterDataController::class, 'expensesDatatable'])->name('expenses.datatable');

                // Category Income
                Route::get('/income', [MasterDataController::class, 'incomeIndex'])->name('income.index');
                Route::get('/income/datatable', [MasterDataController::class, 'incomeDatatable'])->name('income.datatable');

                // Shared Store/Update/Destroy for Categories (bisa dipakai ulang atau dipisah jika perlu validasi beda)
                Route::post('/categories', [MasterDataController::class, 'storeCategory'])->name('categories.store');
                Route::put('/categories/{id}', [MasterDataController::class, 'updateCategory'])->name('categories.update');
                Route::delete('/categories/{id}', [MasterDataController::class, 'destroyCategory'])->name('categories.destroy');

                // Investments
                Route::get('/investments', [MasterDataController::class, 'investmentsIndex'])->name('investments.index');
                Route::get('/investments/datatable', [MasterDataController::class, 'investmentsDatatable'])->name('investments.datatable');
                Route::post('/investments', [MasterDataController::class, 'storeInvestment'])->name('investments.store');
                Route::put('/investments/{id}', [MasterDataController::class, 'updateInvestment'])->name('investments.update');
                Route::delete('/investments/{id}', [MasterDataController::class, 'destroyInvestment'])->name('investments.destroy');

                // Finance Settings
                Route::get('/settings', [MasterDataController::class, 'settingsIndex'])->name('settings.index');
                Route::post('/settings', [MasterDataController::class, 'storeSetting'])->name('settings.store');
            });
        });
    });

    Route::middleware(['can:daily-planner.dashboard'])->group(function () {
        Route::prefix('daily-planner')->name('daily-planner.')->group(function () {
            // Dashboard
            Route::get('/dashboard', [DailyPlannerController::class, 'index'])->name('dashboard');

            // Activity CRUD List
            Route::get('/activity', [DailyPlannerController::class, 'activity'])->name('activity');

            // CRUD Actions
            Route::post('/activity', [DailyPlannerController::class, 'store'])->name('activity.store');
            Route::put('/activity/{id}', [DailyPlannerController::class, 'update'])->name('activity.update');
            Route::delete('/activity/{id}', [DailyPlannerController::class, 'destroy'])->name('activity.destroy');

            // FullCalendar feed
            Route::get('/events', [DailyPlannerController::class, 'getEvents'])->name('events');

            // Quick toggle status
            Route::patch('/activity/{id}/toggle', [DailyPlannerController::class, 'quickToggleStatus'])->name('activity.toggle');

            // Recurring Activities CRUD
            Route::middleware(['can:daily-planner.recurring-activity'])->group(function () {
                Route::get('/recurring-activity', [DailyPlannerController::class, 'recurringActivity'])->name('recurring-activity');
                Route::post('/recurring-activity', [DailyPlannerController::class, 'storeRecurring'])->name('recurring-activity.store');
                Route::put('/recurring-activity/{id}', [DailyPlannerController::class, 'updateRecurring'])->name('recurring-activity.update');
                Route::delete('/recurring-activity/{id}', [DailyPlannerController::class, 'destroyRecurring'])->name('recurring-activity.destroy');
                Route::post('/recurring-activity/sync', [DailyPlannerController::class, 'syncRecurring'])->name('recurring-activity.sync');
            });
        });
    });


    // Account Settings
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/settings', [AccountSettingsController::class, 'index'])->name('settings');
        Route::put('/settings/profile', [AccountSettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password');
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

            // New: User App Access Management
            Route::prefix('user-apps')->name('user-apps.')->group(function () {
                Route::get('/{userId}', [UserAppController::class, 'index'])->name('index');
                Route::get('/roles/{appId}', [UserAppController::class, 'getAppRoles'])->name('roles');
                Route::post('/assign/{userId}', [UserAppController::class, 'assignApp'])->name('assign');
                Route::delete('/remove/{userId}/{appId}', [UserAppController::class, 'removeApp'])->name('remove');
            });
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

        // App System Management Routes
        Route::middleware(['can:permissions'])->group(function () {
            Route::prefix('apps')->name('apps.')->group(function () {
                Route::get('/', [\App\Http\Controllers\AppManagement\AppController::class, 'index'])->name('index');
                Route::get('/datatable', [\App\Http\Controllers\AppManagement\AppController::class, 'datatable'])->name('datatable');
                Route::post('/store', [\App\Http\Controllers\AppManagement\AppController::class, 'store'])->name('store');
                Route::get('/edit/{id}', [\App\Http\Controllers\AppManagement\AppController::class, 'edit'])->name('edit');
                Route::put('/update/{id}', [\App\Http\Controllers\AppManagement\AppController::class, 'update'])->name('update');
                Route::delete('/destroy/{id}', [\App\Http\Controllers\AppManagement\AppController::class, 'destroy'])->name('destroy');
            });
            Route::prefix('app-roles')->name('app-roles.')->group(function () {
                Route::get('/', [\App\Http\Controllers\AppManagement\AppRoleController::class, 'index'])->name('index');
                Route::get('/datatable', [\App\Http\Controllers\AppManagement\AppRoleController::class, 'datatable'])->name('datatable');
                Route::post('/store', [\App\Http\Controllers\AppManagement\AppRoleController::class, 'store'])->name('store');
                Route::get('/edit/{id}', [\App\Http\Controllers\AppManagement\AppRoleController::class, 'edit'])->name('edit');
                Route::put('/update/{id}', [\App\Http\Controllers\AppManagement\AppRoleController::class, 'update'])->name('update');
                Route::delete('/destroy/{id}', [\App\Http\Controllers\AppManagement\AppRoleController::class, 'destroy'])->name('destroy');
            });
            Route::prefix('app-permissions')->name('app-permissions.')->group(function () {
                Route::get('/', [\App\Http\Controllers\AppManagement\AppPermissionController::class, 'index'])->name('index');
                Route::get('/datatable', [\App\Http\Controllers\AppManagement\AppPermissionController::class, 'datatable'])->name('datatable');
                Route::post('/store', [\App\Http\Controllers\AppManagement\AppPermissionController::class, 'store'])->name('store');
                Route::get('/edit/{id}', [\App\Http\Controllers\AppManagement\AppPermissionController::class, 'edit'])->name('edit');
                Route::put('/update/{id}', [\App\Http\Controllers\AppManagement\AppPermissionController::class, 'update'])->name('update');
                Route::delete('/destroy/{id}', [\App\Http\Controllers\AppManagement\AppPermissionController::class, 'destroy'])->name('destroy');
            });
        });
    });

    Route::get('/dashboard', function () {
        return redirect()->route('dashboard'); // Redirect to actual dashboard route
    })->name('dashboard.redirect');
});
