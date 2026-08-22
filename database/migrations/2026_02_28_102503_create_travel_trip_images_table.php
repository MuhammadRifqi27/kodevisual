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
        Schema::create('travel_trip_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('travel_trips')->onDelete('cascade');
            $table->string('filename');             // nama file aslinya
            $table->string('path');                 // path relatif di public
            $table->string('url');                  // full URL yg bisa langsung dipakai di view
            $table->boolean('is_cover')->default(false); // apakah ini gambar cover?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_trip_images');
    }
};
