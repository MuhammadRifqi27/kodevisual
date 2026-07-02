# Changelog Stagging Semantic Version

## [1.17.0] - 2026-07-03

Not yet committed. Investment module rework (Portfolio, Bitcoin/Stocks Tracking, Internal Transfers) plus in-progress Travel Planner work.

### Added
- **Investment** sidebar menu with **Crypto** and **Stocks** submenus, replacing the old standalone "Bitcoin Tracking" item (`config/menu.php`).
- New **Stocks Tracking** page (`StockTrackingController.php`, `stock-tracking/index.blade.php`), mirroring Bitcoin Tracking.
- `type` classification (`crypto` / `stock` / `other`) on investment providers, editable from Master Data → Investments.
- `account_investment` flag on portfolio accounts, editable via a new Edit action on the Portfolio page.
- **Asset / Emiten** tagging on Investment Transactions and Internal Transfers, so buying an asset via bank transfer now shows up in Crypto/Stocks Tracking.
- **Lot** tracking for stock transactions (Investment Transactions + Internal Transfers), with a per-emiten lot breakdown on Stocks Tracking.
- Full **Edit** action for Internal Transfers and for Portfolio accounts (previously add/delete only).
- Data migration merging duplicate one-row-per-asset portfolio accounts into one row per broker.
- Description field for Travel Itinerary activities (trip detail page + add/edit modals).
- Indonesian-localized date formatting on trip day headers.

### Changed
- Bitcoin/Stocks Tracking activity feeds now read from real Investment Transaction and asset-tagged Transfer records.
- Bitcoin Tracking's crypto-account detection switched from name/code substring matching to the explicit `finance_investments.type` field.
- Travel trip "Export Full" / "Export Itinerary" downloads rewritten from plain CSV to styled `.xlsx` workbooks (PhpSpreadsheet), including a per-person price breakdown.

### Fixed
- Portfolio balance double-counting risk removed when tagging asset/lot on a Transfer instead of requiring a second Investment Transaction.

---

## [1.16.0] - 2026-06-08
### Added
- `number_of_persons` on Travel Trips and `cost_per_person` on Travel Itineraries, with per-person cost math.
### Changed
- Trip create/edit/show views and itinerary modals updated to capture and display per-person pricing.

## [1.15.2] - 2026-06-08
### Fixed
- Stale/incorrect "last month" date handling in `BudgetController`, `MoneyManagementDashboardController`, and `SummaryController`.

## [1.15.1] - 2026-06-07
### Fixed
- Daily Planner controller bugs and Money Management dashboard calculation issues.

## [1.15.0] - 2026-05-30
### Added
- Recurring Activity engine for Daily Planner: `GenerateRecurringActivities` console command, `DailyPlannerRecurringActivity` model/table, and a dedicated recurring-activity management page.

## [1.14.1] - 2026-05-30
### Fixed
- Flatpickr time-picker behavior on Daily Planner activity/index pages, plus documentation tweaks.

## [1.14.0] - 2026-05-29
### Added
- **Daily Planner** module: `DailyPlannerController`, `DailyPlannerActivity` model, activity/index views, and seeder.

## [1.13.0] - 2026-05-04
### Added
- Balance visibility toggle ("show/hide saldo") on the Money Management dashboard and Transactions page.

## [1.12.2] - 2026-05-03
### Changed
- Removed the `text-dark` styling class from dashboard and transactions views for consistent theming.

## [1.12.1] - 2026-05-03
### Changed
- Added `permissionType` entries to `config/menu.php`.

## [1.12.0] - 2026-05-03
### Added
- **Bitcoin Tracking** page: `BtcTrackingController`, dedicated view, and sidebar menu entry.

## [1.11.0] - 2026-05-03
### Changed
- Money Management Summary page rewritten to be driven directly by transaction data (major rewrite of `summary/index.blade.php` and `MoneyManagementDashboardController`).

## [1.10.0] - 2026-04-29
### Added
- Chart-based redesign of the Summary page, with supporting `finance` settings.

## [1.9.0] - 2026-04-26
### Added
- `WeddingSavingsTransaction` model/table.
### Changed
- Expanded filtering on the Transactions history page and the Wedding Planner page.

## [1.8.0] - 2026-04-22
### Added
- **Wedding Planner** module (`WeddingPlannerController`, `WeddingPlan`, `WeddingPlannerItem`) with its own page and menu.
- Category filter on the Transactions page.
### Fixed
- Summary graphic rendering issue.
### Changed
- `config/menu.php` restructured/cleaned up.

## [1.7.0] - 2026-03-12
### Added
- **Travel Planner** module: Trips, Budgets, Expenses, Itinerary, Dashboard, and trip-image handling, with full CRUD controllers/views and seeder.
### Fixed
- Monthly budget payroll calculation.

## [1.6.3] - 2026-02-08
### Fixed
- `TransferController` bugs and Money Management dashboard adjustments.

## [1.6.2] - 2026-02-01
### Fixed
- Internal Transfer recording bug.
### Changed
- Migration repointing `finance_transactions` / `finance_investment_transactions` foreign keys to `finance_portfolios`.

## [1.6.1] - 2026-02-01
### Added
- Multi-app structure: `AppController`, `AppRoleController`, `AppPermissionController`, `UserAppController`, plus forgot/reset-password pages.
### Changed
- Budget controller flow and authentication flow refinements.

## [1.6.0] - 2026-02-01
### Added
- **Monthly Budget** module, `RecurringTransactionController`, `FinanceNetWorthSnapshot`, and an Internal Transfer receipt page.
### Changed
- Portfolios split/scoped by `user_id`; transactions migrated to reference portfolios directly.

## [1.5.0] - 2026-02-01
### Added
- Account Settings page/controller, Money Management Summary controller/page, Internal Transfers controller/page, Finance Settings, avatar upload, and `type = transfer` support on `finance_transactions`.

## [1.4.1] - 2026-01-31
### Changed
- Simplified the user-account dropdown menu markup.

## [1.4.0] - 2026-01-30
### Added
- **Money Management** module baseline: Portfolio, Transactions, Master Data (expense/income categories, investments), Dashboard, and the core `finance_*` tables/models. Marked as the first feature-complete milestone.

## [1.3.0] - 2026-01-04
### Added
- Role & Permission management (`RoleController`, `PermissionController`, permission–role pivot) and admin Roles/Permissions/User List pages.

## [1.2.1] - 2025-12-28
### Changed
- Theme color adjustments across auth pages and the sidebar footer.

## [1.2.0] - 2025-12-28
### Added
- Authentication system: login/register (`AuthController`), `Role` model, user-approval workflow, `EnsureUserIsApproved`/`EnsureUserIsAdministrator` middleware, and auth layout/pages.

## [1.1.0] - 2025-08-21
### Added
- `CurrencyHelper` / `GlobalHelper`, and a dedicated "Detail Expenses Rifqi" module (renamed from the generic Detail Expenses controller) with its own repository/service layer.

## [1.0.0] - 2025-07-23
### Added
- Initial Metronic-based Laravel scaffold: base theme/menu system, auth boilerplate, Detail Expenses and Master Category modules.
