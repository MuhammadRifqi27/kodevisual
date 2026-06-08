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
            $table->string('cost_type')->default('total')->after('cost_estimate'); // 'total' or 'per_person'
            $table->decimal('cost_per_person', 15, 2)->default(0)->after('cost_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_itineraries', function (Blueprint $table) {
            $table->dropColumn(['cost_type', 'cost_per_person']);
        });
    }
};
