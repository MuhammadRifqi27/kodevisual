<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Bindings for the Money Management Repository + Service refactor.
 * Kept separate from AppServiceProvider so this bounded context's
 * bindings don't mix with system-level (Auth/Role) ones.
 *
 * See docs/architecture/repository-service-pattern.md.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // FinanceCycle / FinancialAdvice (shared calculators, not model-backed)
        $this->app->bind(
            \App\Services\FinanceCycle\FinanceCycleService::class,
            \App\Services\FinanceCycle\FinanceCycleServiceImplement::class
        );
        $this->app->bind(
            \App\Services\FinancialAdvice\FinancialAdviceService::class,
            \App\Services\FinancialAdvice\FinancialAdviceServiceImplement::class
        );

        // FinanceSetting
        $this->app->bind(
            \App\Repositories\FinanceSetting\FinanceSettingRepository::class,
            \App\Repositories\FinanceSetting\FinanceSettingRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceSetting\FinanceSettingService::class,
            \App\Services\FinanceSetting\FinanceSettingServiceImplement::class
        );

        // FinanceCategory / FinanceInvestment (Master Data)
        $this->app->bind(
            \App\Repositories\FinanceCategory\FinanceCategoryRepository::class,
            \App\Repositories\FinanceCategory\FinanceCategoryRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceCategory\FinanceCategoryService::class,
            \App\Services\FinanceCategory\FinanceCategoryServiceImplement::class
        );
        $this->app->bind(
            \App\Repositories\FinanceInvestment\FinanceInvestmentRepository::class,
            \App\Repositories\FinanceInvestment\FinanceInvestmentRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceInvestment\FinanceInvestmentService::class,
            \App\Services\FinanceInvestment\FinanceInvestmentServiceImplement::class
        );

        // FinancePortfolio (also binds FinanceInvestmentTransaction / FinanceTransaction repositories it depends on)
        $this->app->bind(
            \App\Repositories\FinanceInvestmentTransaction\FinanceInvestmentTransactionRepository::class,
            \App\Repositories\FinanceInvestmentTransaction\FinanceInvestmentTransactionRepositoryImplement::class
        );
        $this->app->bind(
            \App\Repositories\FinanceTransaction\FinanceTransactionRepository::class,
            \App\Repositories\FinanceTransaction\FinanceTransactionRepositoryImplement::class
        );
        $this->app->bind(
            \App\Repositories\FinancePortfolio\FinancePortfolioRepository::class,
            \App\Repositories\FinancePortfolio\FinancePortfolioRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinancePortfolio\FinancePortfolioService::class,
            \App\Services\FinancePortfolio\FinancePortfolioServiceImplement::class
        );

        // FinanceTransaction / FinanceTransfer
        $this->app->bind(
            \App\Services\FinanceTransaction\FinanceTransactionService::class,
            \App\Services\FinanceTransaction\FinanceTransactionServiceImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceTransfer\FinanceTransferService::class,
            \App\Services\FinanceTransfer\FinanceTransferServiceImplement::class
        );

        // FinanceBudget / FinanceRecurringTransaction
        $this->app->bind(
            \App\Repositories\FinanceBudget\FinanceBudgetRepository::class,
            \App\Repositories\FinanceBudget\FinanceBudgetRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceBudget\FinanceBudgetService::class,
            \App\Services\FinanceBudget\FinanceBudgetServiceImplement::class
        );
        $this->app->bind(
            \App\Repositories\FinanceRecurringTransaction\FinanceRecurringTransactionRepository::class,
            \App\Repositories\FinanceRecurringTransaction\FinanceRecurringTransactionRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceRecurringTransaction\FinanceRecurringTransactionService::class,
            \App\Services\FinanceRecurringTransaction\FinanceRecurringTransactionServiceImplement::class
        );

        // FinanceIpoOrder
        $this->app->bind(
            \App\Repositories\FinanceIpoOrder\FinanceIpoOrderRepository::class,
            \App\Repositories\FinanceIpoOrder\FinanceIpoOrderRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceIpoOrder\FinanceIpoOrderService::class,
            \App\Services\FinanceIpoOrder\FinanceIpoOrderServiceImplement::class
        );

        // FinanceNetWorthSnapshot / FinanceDashboard / FinanceSummary
        $this->app->bind(
            \App\Repositories\FinanceNetWorthSnapshot\FinanceNetWorthSnapshotRepository::class,
            \App\Repositories\FinanceNetWorthSnapshot\FinanceNetWorthSnapshotRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceDashboard\FinanceDashboardService::class,
            \App\Services\FinanceDashboard\FinanceDashboardServiceImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceSummary\FinanceSummaryService::class,
            \App\Services\FinanceSummary\FinanceSummaryServiceImplement::class
        );

        // FinanceBtcTracking
        $this->app->bind(
            \App\Services\FinanceBtcTracking\FinanceBtcTrackingService::class,
            \App\Services\FinanceBtcTracking\FinanceBtcTrackingServiceImplement::class
        );

        // FinanceStockTracking (also binds FinanceEmitenTrade / FinanceEmitenPrice repositories)
        $this->app->bind(
            \App\Repositories\FinanceEmitenTrade\FinanceEmitenTradeRepository::class,
            \App\Repositories\FinanceEmitenTrade\FinanceEmitenTradeRepositoryImplement::class
        );
        $this->app->bind(
            \App\Repositories\FinanceEmitenPrice\FinanceEmitenPriceRepository::class,
            \App\Repositories\FinanceEmitenPrice\FinanceEmitenPriceRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\FinanceStockTracking\FinanceStockTrackingService::class,
            \App\Services\FinanceStockTracking\FinanceStockTrackingServiceImplement::class
        );

        // WeddingPlanner (optional/last batch — web-only, no web/API duplication to remove)
        $this->app->bind(
            \App\Repositories\WeddingPlan\WeddingPlanRepository::class,
            \App\Repositories\WeddingPlan\WeddingPlanRepositoryImplement::class
        );
        $this->app->bind(
            \App\Repositories\WeddingPlannerItem\WeddingPlannerItemRepository::class,
            \App\Repositories\WeddingPlannerItem\WeddingPlannerItemRepositoryImplement::class
        );
        $this->app->bind(
            \App\Repositories\WeddingSavingsTransaction\WeddingSavingsTransactionRepository::class,
            \App\Repositories\WeddingSavingsTransaction\WeddingSavingsTransactionRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\WeddingPlanner\WeddingPlannerService::class,
            \App\Services\WeddingPlanner\WeddingPlannerServiceImplement::class
        );
    }
}
