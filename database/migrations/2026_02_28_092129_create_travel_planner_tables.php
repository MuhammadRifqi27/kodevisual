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
        Schema::create('travel_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->string('currency')->default('IDR');
            $table->enum('status', ['draft', 'planned', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        Schema::create('travel_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('travel_trips')->onDelete('cascade');
            $table->integer('day_number');
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('activity');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cost_estimate', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('travel_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('travel_trips')->onDelete('cascade');
            $table->string('category');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('travel_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('travel_trips')->onDelete('cascade');
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->boolean('is_pre_trip')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_expenses');
        Schema::dropIfExists('travel_budgets');
        Schema::dropIfExists('travel_itineraries');
        Schema::dropIfExists('travel_trips');
    }
};
