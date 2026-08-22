<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update finance_transactions
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropForeign(['finance_investment_id']);
            $table->dropForeign(['to_finance_investment_id']);
            
            $table->foreign('finance_investment_id')
                ->references('id')->on('finance_portfolios')
                ->onDelete('set null');
                
            $table->foreign('to_finance_investment_id')
                ->references('id')->on('finance_portfolios')
                ->onDelete('cascade');
        });

        // 2. Update finance_investment_transactions
        Schema::table('finance_investment_transactions', function (Blueprint $table) {
            $table->dropForeign(['finance_investment_id']);
            
            $table->foreign('finance_investment_id')
                ->references('id')->on('finance_portfolios')
                ->onDelete('cascade');
        });

        // 3. Update finance_recurring_transactions
        Schema::table('finance_recurring_transactions', function (Blueprint $table) {
            $table->dropForeign(['finance_investment_id']);
            
            $table->foreign('finance_investment_id')
                ->references('id')->on('finance_portfolios')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting would require pointing back to finance_investments, 
        // which might fail if IDs no longer exist there.
    }
};
