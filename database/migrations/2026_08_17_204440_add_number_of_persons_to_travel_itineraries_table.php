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
        Schema::table('travel_itineraries', function (Blueprint $table) {
            $table->unsignedInteger('number_of_persons')->nullable()->after('cost_per_person');
        });

        // Backfill existing activities with their trip's default persons count.
        \Illuminate\Support\Facades\DB::statement(
            'UPDATE travel_itineraries ti
             INNER JOIN travel_trips tt ON tt.id = ti.trip_id
             SET ti.number_of_persons = tt.number_of_persons'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_itineraries', function (Blueprint $table) {
            $table->dropColumn('number_of_persons');
        });
    }
};
