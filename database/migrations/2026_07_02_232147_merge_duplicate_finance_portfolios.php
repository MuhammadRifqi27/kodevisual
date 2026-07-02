<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Historically a `finance_portfolios` row was created per (broker + asset) combo,
     * e.g. broker "FLOQ" had separate rows named "BITCOIN", "ETHEREUM", "HYPERLIQUID".
     * This collapses each (user_id, finance_investment_id) group down to a single
     * broker-level row, tagging the previously-implicit asset identity onto the
     * individual `finance_investment_transactions` rows instead.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $groups = DB::table('finance_portfolios')
                ->select('user_id', 'finance_investment_id')
                ->distinct()
                ->orderBy('finance_investment_id')
                ->get();

            foreach ($groups as $group) {
                $rows = DB::table('finance_portfolios')
                    ->where('user_id', $group->user_id)
                    ->where('finance_investment_id', $group->finance_investment_id)
                    ->orderBy('id')
                    ->get();

                $survivor = $rows->first();

                foreach ($rows as $row) {
                    // Tag this row's own investment transactions with what its
                    // account_name used to represent, before it gets reassigned/renamed.
                    DB::table('finance_investment_transactions')
                        ->where('finance_investment_id', $row->id)
                        ->whereNull('asset')
                        ->update(['asset' => $row->account_name]);

                    if ($row->id === $survivor->id) {
                        continue;
                    }

                    // Reassign every FK that pointed at this duplicate row to the survivor.
                    DB::table('finance_investment_transactions')
                        ->where('finance_investment_id', $row->id)
                        ->update(['finance_investment_id' => $survivor->id]);

                    DB::table('finance_transactions')
                        ->where('finance_investment_id', $row->id)
                        ->update(['finance_investment_id' => $survivor->id]);

                    DB::table('finance_transactions')
                        ->where('to_finance_investment_id', $row->id)
                        ->update(['to_finance_investment_id' => $survivor->id]);

                    DB::table('finance_recurring_transactions')
                        ->where('finance_investment_id', $row->id)
                        ->update(['finance_investment_id' => $survivor->id]);

                    // Safe now: nothing references this row anymore.
                    DB::table('finance_portfolios')->where('id', $row->id)->delete();
                }

                $brokerName = DB::table('finance_investments')
                    ->where('id', $group->finance_investment_id)
                    ->value('name');

                DB::table('finance_portfolios')
                    ->where('id', $survivor->id)
                    ->update(['account_name' => $brokerName]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Not reversible: duplicate rows and their original per-asset account_name
     * values are gone once merged. Same accepted limitation as the existing
     * 2026_02_01_223010_update_finance_foreign_keys_to_portfolios migration.
     */
    public function down(): void
    {
        //
    }
};
