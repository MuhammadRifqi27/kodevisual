# Portfolio Management Refactoring Summary

## Tanggal: 2026-02-01

## Tujuan Refactoring

Memisahkan data global investment platforms (seperti BCA, Mandiri, Tokocrypto) dari user-specific account instances untuk mendukung multi-user environment dengan data isolation yang proper.

## Perubahan Database

### 1. Tabel Baru: `finance_portfolios`

- **Tujuan**: Menyimpan akun spesifik user yang terhubung ke global investment platforms
- **Kolom Utama**:
    - `user_id` - Foreign key ke users
    - `finance_investment_id` - Foreign key ke finance_investments (global platforms)
    - `account_name` - Nama akun user (misal: "BCA Tabungan Pribadi")
    - `account_number` - Nomor rekening (opsional)
    - `description` - Deskripsi tambahan

### 2. Migrasi Data

- Semua data user-specific dari `finance_investments` dipindahkan ke `finance_portfolios`
- Semua referensi `finance_investment_id` di tabel transaksi diupdate untuk menunjuk ke portfolio ID yang baru
- Tabel yang diupdate:
    - `finance_transactions`
    - `finance_investment_transactions`
    - `finance_recurring_transactions`

## Perubahan Model

### 1. FinancePortfolio (Baru)

```php
- investment() // BelongsTo FinanceInvestment
- user() // BelongsTo User
- transactions() // HasMany FinanceInvestmentTransaction
- generalTransactions() // HasMany FinanceTransaction
- balance // Attribute (calculated)
```

### 2. FinanceTransaction

- ❌ `investment()` → ✅ `portfolio()` // BelongsTo FinancePortfolio
- ❌ `destinationAccount()` → ✅ `destinationPortfolio()` // BelongsTo FinancePortfolio

### 3. FinanceRecurringTransaction

- ❌ `investment()` → ✅ `portfolio()` // BelongsTo FinancePortfolio

### 4. FinanceInvestmentTransaction

- ❌ `investment()` → ✅ `portfolio()` // BelongsTo FinancePortfolio

### 5. FinanceInvestment

- Dihapus: `user_id` column
- Ditambah: `hasMany(FinancePortfolio::class)`
- Sekarang berfungsi sebagai **global master data**

## Perubahan Controller

### 1. PortfolioController

- `index()` - Fetch global investments untuk dropdown
- `datatable()` - Menampilkan user portfolios dengan eager loading investment
- `store()` - Create portfolio baru untuk user
- `destroy()` - Delete portfolio (dengan validasi transaksi)
- `show()` - Menampilkan detail portfolio dengan balance
- `transactionDatatable()` - Menampilkan transaksi portfolio

### 2. MoneyManagementDashboardController

- Menggunakan `FinancePortfolio::where('user_id', $userId)` instead of `FinanceInvestment::all()`
- Updated balance calculation untuk menggunakan portfolio ID
- Updated heuristic untuk menggunakan `$portfolio->investment->name`
- Fixed `with(['category', 'portfolio'])` untuk recent transactions

### 3. SummaryController

- Menggunakan `FinancePortfolio` untuk net worth calculation
- Asset allocation menggunakan portfolio account names

### 4. TransactionController

- `index()` - Fetch user portfolios untuk dropdown
- `datatable()` - Eager load `portfolio` relationship
- Validation menggunakan `finance_portfolios` table
- User ownership check untuk portfolio

### 5. TransferController

- Menggunakan `FinancePortfolio` untuk source dan destination accounts
- Updated validation dan user checks
- Store/destroy logic menggunakan portfolio relationships

### 6. RecurringTransactionController

- Fetch user portfolios untuk account selection
- Updated validation dan relationships

## Perubahan View

### 1. portfolio/index.blade.php

- Added "Add New Account" button dan modal
- Modal untuk memilih global investment dan membuat account name
- Updated datatable columns untuk menampilkan account_name
- Added delete functionality dengan SweetAlert

### 2. portfolio/show.blade.php

- Updated untuk menggunakan `$portfolio` variable
- Menampilkan provider name dari relationship

### 3. transactions/index.blade.php

- Updated dropdown untuk menampilkan `account_name`
- Updated datatable column reference ke `portfolio.account_name`

### 4. transactions/recurring.blade.php

- Updated account selection dropdown
- Updated datatable column reference

### 5. dashboard.blade.php

- Sudah menggunakan data dari controller yang sudah direfactor

## Testing Checklist

- [ ] Dashboard loads tanpa error
- [ ] Portfolio list menampilkan user accounts
- [ ] Add new account berfungsi
- [ ] Delete account berfungsi (dengan validasi)
- [ ] Transaction creation dengan portfolio selection
- [ ] Transfer between portfolios
- [ ] Recurring transaction setup
- [ ] Balance calculation akurat
- [ ] Multi-user isolation (test dengan 2 user berbeda)

## Catatan Penting

1. **Data Integrity**: Semua transaksi existing sudah dimigrate dengan benar
2. **User Isolation**: Setiap query sudah include `where('user_id', auth()->id())`
3. **Backward Compatibility**: Tidak ada - ini breaking change yang memerlukan migration
4. **Performance**: Balance calculation menggunakan attribute accessor (consider caching untuk production)

## Next Steps

1. Resolve Breadcrumbs lint errors (low priority)
2. Implement net worth trend chart
3. Implement asset allocation chart
4. Add snapshot functionality
5. Setup cron job untuk recurring transactions
6. Add edit functionality untuk recurring transactions
