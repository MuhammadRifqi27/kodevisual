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
        Schema::table('finance_investment_transactions', function (Blueprint $table) {
            $table->integer('lot')->nullable()->after('asset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_investment_transactions', function (Blueprint $table) {
            $table->dropColumn('lot');
        });
    }
};
