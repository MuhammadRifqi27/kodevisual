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
        Schema::create('travel_packing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('travel_trips')->onDelete('cascade');
            $table->string('category')->nullable();
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->boolean('is_packed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_packing_items');
    }
};
