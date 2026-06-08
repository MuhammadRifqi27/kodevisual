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
        Schema::table('travel_trips', function (Blueprint $table) {
            $table->integer('number_of_persons')->default(1)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_trips', function (Blueprint $table) {
            $table->dropColumn('number_of_persons');
        });
    }
};
