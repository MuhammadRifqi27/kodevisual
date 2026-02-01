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
        // 1. Data Migration: For each investment that has a user_id, create a portfolio record
        $investments = DB::table('finance_investments')->whereNotNull('user_id')->get();
        
        foreach ($investments as $inv) {
            $portfolioId = DB::table('finance_portfolios')->insertGetId([
                'user_id' => $inv->user_id,
                'finance_investment_id' => $inv->id,
                'account_name' => $inv->name,
                'description' => $inv->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Update all transactions that were pointing to this investment to point to the new portfolio ID
            DB::table('finance_transactions')->where('finance_investment_id', $inv->id)->update(['finance_investment_id' => $portfolioId]);
            DB::table('finance_transactions')->where('to_finance_investment_id', $inv->id)->update(['to_finance_investment_id' => $portfolioId]);
            DB::table('finance_investment_transactions')->where('finance_investment_id', $inv->id)->update(['finance_investment_id' => $portfolioId]);
            DB::table('finance_recurring_transactions')->where('finance_investment_id', $inv->id)->update(['finance_investment_id' => $portfolioId]);
        }

        // 3. Clean up finance_investments (remove user-specific data and the column)
        // We will keep 'name', 'code', 'description' but they should be unique-ish global templates.
        // For now just remove user_id to make it global.
        Schema::table('finance_investments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding user_id is complex due to data movements, skipping for now as this is a specific structural change.
    }
};
