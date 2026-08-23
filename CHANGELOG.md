# Changelog Stagging Semantic Version

## [1.0.1] - 2026-08-23

### Changed
- Refactored the entire Money Management module (web + API, 25 controllers) to a Repository + Service architecture — no intentional user-facing behavior change beyond the fixes below. See `docs/repository-service-pattern.md` and `docs/money-management-refactor.md`.

### Fixed
- Recurring transactions generated from the web Recurring page now store a positive `amount` (sign carried by `type`), matching the API and manual transactions — previously expense amounts were double-negated when read back into account balances.
- Mobile API dashboard's investment/liquid-cash split now also recognizes investment id `11` (AJAIB) as an investment account, matching the web dashboard (previously only the web side had this).
- Portfolio ledger entries created/edited from the web Portfolio page are now restricted to `deposit|withdrawal|profit|loss`, matching the API's validation (previously any string was accepted).

## [1.0.0] - 2026-08-22

### Added

**Core & Access Control**
- Initial Metronic-based Laravel scaffold: base theme/menu system, auth boilerplate.
- Authentication system: login/register, `Role` model, user-approval workflow, `EnsureUserIsApproved` / `EnsureUserIsAdministrator` middleware, and auth layout/pages.
- Role & Permission management (`RoleController`, `PermissionController`, permission–role pivot) and admin Roles/Permissions/User List pages.
- Multi-app structure: `AppController`, `AppRoleController`, `AppPermissionController`, `UserAppController`, plus forgot/reset-password pages.
- `CurrencyHelper` / `GlobalHelper`, and a dedicated "Detail Expenses Rifqi" module with its own repository/service layer.

**Money Management**
- Core module baseline: Portfolio, Transactions, Master Data (expense/income categories, investments), Dashboard, and the core `finance_*` tables/models.
- Account Settings page, Money Management Summary controller/page, Internal Transfers controller/page, Finance Settings, avatar upload, and `type = transfer` support on `finance_transactions`.
- **Monthly Budget** module, `RecurringTransactionController`, `FinanceNetWorthSnapshot`, and an Internal Transfer receipt page.
- Chart-based redesign of the Summary page, later rewritten to be driven directly by transaction data.
- Balance visibility toggle ("show/hide saldo") on the dashboard and Transactions page.
- **Bitcoin Tracking** page, later folded into an **Investment** sidebar menu with **Crypto** and **Stocks** submenus.
- **Stocks Tracking** page mirroring Bitcoin Tracking, later expanded with IPO-sourced holdings and richer per-emiten activity detail.
- **IPO Stocks** page (`IpoController`) for recording IPO subscriptions/allotments; `FinanceIpoOrder`, `FinanceEmitenPrice`, `FinanceEmitenTrade` models/tables.
- `type` classification (`crypto` / `stock` / `other`) on investment providers, editable from Master Data → Investments.
- `account_investment` flag on portfolio accounts, editable via a new Edit action on the Portfolio page.
- **Asset / Emiten** tagging on Investment Transactions and Internal Transfers, so buying an asset via bank transfer shows up in Crypto/Stocks Tracking.
- **Lot** tracking for stock transactions (Investment Transactions + Internal Transfers), with a per-emiten lot breakdown on Stocks Tracking.
- Full **Edit** action for Internal Transfers and for Portfolio accounts (previously add/delete only).
- Data migration merging duplicate one-row-per-asset portfolio accounts into one row per broker.

**Wedding Planner**
- **Wedding Planner** module (`WeddingPlannerController`, `WeddingPlan`, `WeddingPlannerItem`) with its own page and menu.
- `WeddingSavingsTransaction` model/table.

**Daily Planner**
- **Daily Planner** module: `DailyPlannerController`, `DailyPlannerActivity` model, activity/index views, and seeder.
- Recurring Activity engine: `GenerateRecurringActivities` console command, `DailyPlannerRecurringActivity` model/table, and a dedicated recurring-activity management page.

**Travel Planner**
- **Travel Planner** module: Trips, Budgets, Expenses, Itinerary, Dashboard, and trip-image handling, with full CRUD controllers/views and seeder.
- Description field for itinerary activities, and Indonesian-localized date formatting on trip day headers.
- `number_of_persons` on Travel Trips and `cost_per_person` on Travel Itineraries, with per-person cost math.
- "Export Full Trip" / "Export Itinerary" downloads as styled `.xlsx` workbooks (PhpSpreadsheet), including a per-person price breakdown.
- **Edit** action for Budget Allocations (previously add/delete only).
- **Packing checklist tab**: add/edit/delete items with category, quantity and notes, a per-item packed checkbox (AJAX toggle), and a progress bar.
- Per-activity **Number of Persons** override on itineraries, so an activity not everyone joins can use its own person count instead of the trip default when splitting total ⇄ per-person cost. Surfaced as a "pax" badge on the itinerary table and as a column in both Excel exports.

**Mobile API**
- **API v1** (`app/Http/Controllers/Api/V1/...`, Laravel Sanctum bearer tokens): auth, dashboard, transactions, budgets, summary, portfolio, transfers, recurring transactions, categories, settings, BTC tracking, stock tracking, and IPO endpoints for the KVWallet mobile app.
- `EnsureApiUserIsApproved` middleware and a dedicated API exception handler for consistent JSON error responses.
- Postman collection and internal docs (mobile app development guide, business-flow/database notes, deployment steps) documenting the API and mobile integration.

### Changed
- Theme color adjustments across auth pages and the sidebar footer; simplified user-account dropdown menu markup; `text-dark` styling removed for consistent theming.
- `config/menu.php` restructured and cleaned up multiple times as modules were added.
- Portfolios split/scoped by `user_id`; transactions migrated to reference portfolios directly.
- Money Management Summary page rewritten to be driven directly by transaction data.
- Bitcoin/Stocks Tracking activity feeds switched to read from real Investment Transaction and asset-tagged Transfer records; crypto-account detection switched from name/code substring matching to the explicit `finance_investments.type` field.
- Trip-level "Edit Details" person-count change now only cascades to itinerary activities still following the trip default; activities with a manually overridden persons count are left untouched.
- Itinerary cost display now always shows the per-person breakdown, even when an activity is priced as a flat Total Cost.

### Fixed
- Monthly budget payroll calculation and Internal Transfer recording bugs.
- Stale/incorrect "last month" date handling in `BudgetController`, `MoneyManagementDashboardController`, and `SummaryController`.
- Daily Planner controller bugs, Flatpickr time-picker behavior, and Summary page graphic rendering.
- Portfolio balance double-counting risk removed when tagging asset/lot on a Transfer instead of requiring a second Investment Transaction.
- My Assets view and table ordering on the Bitcoin/Stock Tracking pages.
- Investment/liquid-cash split on the Money Management dashboard now also recognizes provider id `11` as an investment account.
