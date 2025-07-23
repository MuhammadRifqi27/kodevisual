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
        Schema::create('detail_expenses_rifqi', function (Blueprint $table) {
            $table->id();
            $table->string('detail_expenses');
            $table->decimal('cost', 15, 2)->nullable();
            $table->unsignedBigInteger('equipment_promosi_id')->nullable()->after('id');
            $table->foreign('equipment_promosi_id')->references('id')->on('equipment_promosi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_expenses_rifqi');
    }
};
