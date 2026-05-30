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
        // 1. Create recurring activities table
        Schema::create('daily_planner_recurring_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('activity');
            $table->string('frequency'); // daily, weekly, monthly
            $table->text('day_of_week')->nullable(); // json or comma-separated days for weekly
            $table->integer('day_of_month')->nullable(); // day number (1-31) for monthly
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->default('08:00');
            $table->integer('duration_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add foreign keys and attributes to daily_planner_activities table
        Schema::table('daily_planner_activities', function (Blueprint $table) {
            $table->foreignId('recurring_activity_id')
                ->nullable()
                ->after('user_id')
                ->constrained('daily_planner_recurring_activities')
                ->onDelete('cascade');
            
            $table->date('recurring_date')
                ->nullable()
                ->after('recurring_activity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_planner_activities', function (Blueprint $table) {
            $table->dropForeign(['recurring_activity_id']);
            $table->dropColumn(['recurring_activity_id', 'recurring_date']);
        });

        Schema::dropIfExists('daily_planner_recurring_activities');
    }
};
