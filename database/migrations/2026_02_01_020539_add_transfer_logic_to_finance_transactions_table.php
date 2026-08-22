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
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->enum('type', ['income', 'expense', 'transfer'])->change();
            $table->foreignId('to_finance_investment_id')->nullable()->after('finance_investment_id')->constrained('finance_investments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->enum('type', ['income', 'expense'])->change();
            $table->dropForeign(['to_finance_investment_id']);
            $table->dropColumn('to_finance_investment_id');
        });
    }
};
