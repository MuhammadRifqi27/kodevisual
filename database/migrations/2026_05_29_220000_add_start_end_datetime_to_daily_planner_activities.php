<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_planner_activities', function (Blueprint $table) {
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
        });

        // Migrate existing data (date + time) into start_datetime and end_datetime (default 1 hour duration)
        \DB::table('daily_planner_activities')->orderBy('id')->chunk(100, function ($activities) {
            foreach ($activities as $act) {
                if (!empty($act->date) && !empty($act->time)) {
                    $start = $act->date . ' ' . $act->time;
                    $startDt = \Carbon\Carbon::parse($start);
                    $endDt = $startDt->copy()->addHour();
                    \DB::table('daily_planner_activities')->where('id', $act->id)->update([
                        'start_datetime' => $startDt,
                        'end_datetime'   => $endDt,
                    ]);
                }
            }
        });

        // Drop old columns
        Schema::table('daily_planner_activities', function (Blueprint $table) {
            $table->dropColumn(['date', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_planner_activities', function (Blueprint $table) {
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->dropColumn(['start_datetime', 'end_datetime']);
        });
    }
};
