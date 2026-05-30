<?php

namespace App\Console\Commands;

use App\Models\DailyPlannerActivity;
use App\Models\DailyPlannerRecurringActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily-planner:generate-recurring
                            {--user= : Process only a specific user ID}
                            {--days=14 : Number of days ahead to generate (default: 14)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily activity entries from active recurring activity definitions for all users.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days    = (int) $this->option('days');
        $userId  = $this->option('user');

        $query = User::query();
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('No users found.');
            return self::FAILURE;
        }

        $today   = Carbon::today();
        $endScan = $today->copy()->addDays($days - 1);

        $this->info("Generating recurring activities from {$today->toDateString()} to {$endScan->toDateString()} ...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $totalCreated = 0;

        foreach ($users as $user) {
            $recurrings = DailyPlannerRecurringActivity::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            foreach ($recurrings as $rec) {
                // Scan day-by-day over the window
                $current = $today->copy();

                while ($current->lessThanOrEqualTo($endScan)) {
                    if ($rec->matchesDate($current)) {
                        $exists = DailyPlannerActivity::where('user_id', $user->id)
                            ->where('recurring_activity_id', $rec->id)
                            ->whereDate('recurring_date', $current->toDateString())
                            ->exists();

                        if (! $exists) {
                            $startDt = Carbon::parse($current->toDateString() . ' ' . $rec->start_time);
                            $endDt   = $startDt->copy()->addMinutes($rec->duration_minutes);

                            DailyPlannerActivity::create([
                                'user_id'              => $user->id,
                                'recurring_activity_id' => $rec->id,
                                'recurring_date'       => $current->toDateString(),
                                'activity'             => $rec->activity,
                                'status'               => 'not started',
                                'start_datetime'       => $startDt,
                                'end_datetime'         => $endDt,
                            ]);

                            $totalCreated++;
                        }
                    }

                    $current->addDay();
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done! {$totalCreated} daily activity record(s) generated.");

        return self::SUCCESS;
    }
}
