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
        Schema::create('finance_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('finance_investment_id')->constrained('finance_investments')->onDelete('cascade');
            $table->string('account_name');
            $table->string('account_number')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add foreign keys or rename columns in transaction tables to point to portfolios instead of global investments
        // To avoid breaking existing data immediately, we will:
        // 1. Create portfolios
        // 2. We will eventually need to update transactions. 
        // But for simplicity and maintaining "finance_investment_id" name in transactions, 
        // we can just treat the ID in transactions as referencing "finance_portfolios.id" now.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_portfolios');
    }
};
