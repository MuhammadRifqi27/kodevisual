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
        Schema::create('finance_investment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_investment_id')->constrained('finance_investments')->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['deposit', 'withdrawal', 'profit', 'loss']); // Profit/Loss for referencing adjustments if needed
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_investment_transactions');
    }
};
