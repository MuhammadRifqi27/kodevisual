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
        Schema::create('finance_emiten_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('finance_investment_id')->constrained('finance_portfolios')->onDelete('cascade');
            $table->string('asset', 100);
            $table->enum('type', ['buy', 'sell']);
            $table->date('trade_date');
            $table->decimal('price_per_share', 15, 2);
            $table->unsignedInteger('lot');
            $table->text('description')->nullable();

            // Direct references to the ledger rows this trade generated, so
            // edit/delete is exact instead of relying on fragile matching.
            $table->foreignId('holding_transaction_id')->nullable()->constrained('finance_investment_transactions')->nullOnDelete();
            $table->foreignId('cash_transaction_id')->nullable()->constrained('finance_investment_transactions')->nullOnDelete();
            $table->foreignId('pnl_transaction_id')->nullable()->constrained('finance_investment_transactions')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_emiten_trades');
    }
};
