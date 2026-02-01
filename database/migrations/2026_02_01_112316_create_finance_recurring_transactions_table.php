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
        Schema::create('finance_recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('finance_category_id')->nullable()->constrained('finance_categories')->onDelete('set null');
            $table->foreignId('finance_investment_id')->nullable()->constrained('finance_investments')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('frequency'); // daily, weekly, monthly, yearly
            $table->date('start_date');
            $table->date('next_date');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_recurring_transactions');
    }
};
