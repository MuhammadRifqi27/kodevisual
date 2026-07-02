<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('finance_portfolios', function (Blueprint $table) {
            $table->boolean('account_investment')->default(false)->after('description');
        });

        // Backfill: portfolios already linked to a crypto/stock provider are investment accounts
        DB::table('finance_portfolios')
            ->join('finance_investments', 'finance_investments.id', '=', 'finance_portfolios.finance_investment_id')
            ->whereIn('finance_investments.type', ['crypto', 'stock'])
            ->update(['finance_portfolios.account_investment' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_portfolios', function (Blueprint $table) {
            $table->dropColumn('account_investment');
        });
    }
};
