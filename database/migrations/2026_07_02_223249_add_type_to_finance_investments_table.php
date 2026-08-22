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
        Schema::table('finance_investments', function (Blueprint $table) {
            $table->string('type')->default('other')->after('code'); // crypto, stock, other
        });

        // Backfill existing Bitcoin/BTC providers so Crypto Tracking keeps working
        DB::table('finance_investments')
            ->where('name', 'like', '%Bitcoin%')
            ->orWhere('code', 'like', '%BTC%')
            ->update(['type' => 'crypto']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_investments', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
