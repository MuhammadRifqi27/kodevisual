<?php

namespace App\Http\Controllers;

use App\Models\DailyPlannerActivity;
use App\Models\DailyPlannerRecurringActivity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyPlannerController extends Controller
{
    /**
     * Display the Daily Planner Dashboard.
     */
    public function index()
    {
        addVendor('flatpickr');

        $userId = auth()->id();
        $this->processRecurringActivities($userId);
        $today = Carbon::today()->toDateString();

        // 1. Core Statistics
        $totalTasks = DailyPlannerActivity::where('user_id', $userId)->count();
        $notStartedTasks = DailyPlannerActivity::where('user_id', $userId)->where('status', 'not started')->count();
        $inProgressTasks = DailyPlannerActivity::where('user_id', $userId)->where('status', 'in progress')->count();
        $doneTasks = DailyPlannerActivity::where('user_id', $userId)->where('status', 'done')->count();

        // 2. Today's Statistics & Activities
        $todayActivities = DailyPlannerActivity::where('user_id', $userId)
            ->whereDate('start_datetime', $today)
            ->orderBy('start_datetime', 'asc')
            ->get();

        $todayTotalCount = $todayActivities->count();
        $todayDoneCount = $todayActivities->where('status', 'done')->count();
        $todayCompletionRate = $todayTotalCount > 0 ? round(($todayDoneCount / $todayTotalCount) * 100) : 0;

        // 3. Next Up Activity for Today
        $nowTime = Carbon::now()->toTimeString();
        $now = Carbon::now();
        $nextActivity = DailyPlannerActivity::where('user_id', $userId)
            ->whereDate('start_datetime', $today)
            ->where('status', '!=', 'done')
            ->where('start_datetime', '>=', $now)
            ->orderBy('start_datetime', 'asc')
            ->first();

        // 4. Weekly completion stats (last 7 days productivity chart)
        $weeklyChartData = [];
        $weeklyChartCategories = [];
        Carbon::setLocale('id');

        for ($i = 6; $i >= 0; $i--) {
            $dayDate = Carbon::today()->subDays($i)->toDateString();
            $dayLabel = Carbon::today()->subDays($i)->translatedFormat('D'); // e.g. Sen, Sel, Rab

            $dayTotal = DailyPlannerActivity::where('user_id', $userId)
                ->whereDate('start_datetime', $dayDate)
                ->count();
            $dayDone = DailyPlannerActivity::where('user_id', $userId)
                ->whereDate('start_datetime', $dayDate)
                ->where('status', 'done')
                ->count();

            $rate = $dayTotal > 0 ? round(($dayDone / $dayTotal) * 100) : 0;

            $weeklyChartData[] = $rate;
            $weeklyChartCategories[] = $dayLabel;
        }

        return view('pages.daily-planner.index', compact(
            'totalTasks',
            'notStartedTasks',
            'inProgressTasks',
            'doneTasks',
            'todayActivities',
            'todayTotalCount',
            'todayDoneCount',
            'todayCompletionRate',
            'nextActivity',
            'weeklyChartData',
            'weeklyChartCategories'
        ));
    }

    /**
     * Display the Activity Listing & Calendar page.
     */
    public function activity()
    {
        addVendor('fullcalendar');
        addVendor('flatpickr');

        $userId = auth()->id();
        $this->processRecurringActivities($userId);
        $activities = DailyPlannerActivity::where('user_id', $userId)
            ->orderBy('start_datetime', 'desc')
            ->get();

        return view('pages.daily-planner.activity', compact('activities'));
    }

    /**
     * Store a newly created activity.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_datetime' => 'required|date_format:Y-m-d H:i',
            'end_datetime'   => 'nullable|date_format:Y-m-d H:i|after:start_datetime',
            'activity'       => 'required|string|max:255',
            'status'         => 'required|in:not started,in progress,done',
        ]);

        $start = $request->start_datetime;
        $end   = $request->end_datetime;
        if (empty($end)) {
            $end = Carbon::parse($start)->addHour()->format('Y-m-d H:i');
        }

        DailyPlannerActivity::create([
            'user_id'        => auth()->id(),
            'activity'       => $request->activity,
            'status'         => $request->status,
            'start_datetime' => $start,
            'end_datetime'   => $end,
        ]);

        return response()->json(['success' => 'Kegiatan harian berhasil ditambahkan!']);
    }

    /**
     * Update the specified activity.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'start_datetime' => 'required|date_format:Y-m-d H:i',
            'end_datetime'   => 'nullable|date_format:Y-m-d H:i|after:start_datetime',
            'activity'       => 'required|string|max:255',
            'status'         => 'required|in:not started,in progress,done',
        ]);

        $activity = DailyPlannerActivity::where('user_id', auth()->id())->findOrFail($id);

        $end = $request->end_datetime;
        if (empty($end)) {
            $end = Carbon::parse($request->start_datetime)->addHour()->format('Y-m-d H:i');
        }

        $activity->update([
            'activity'       => $request->activity,
            'status'         => $request->status,
            'start_datetime' => $request->start_datetime,
            'end_datetime'   => $end,
        ]);

        return response()->json(['success' => 'Kegiatan harian berhasil diperbarui!']);
    }

    /**
     * Remove the specified activity.
     */
    public function destroy($id)
    {
        $activity = DailyPlannerActivity::where('user_id', auth()->id())->findOrFail($id);
        $activity->delete();

        return response()->json(['success' => 'Kegiatan harian berhasil dihapus!']);
    }

    /**
     * Fetch events for FullCalendar feed.
     */
    public function getEvents()
    {
        $userId = auth()->id();
        $activities = DailyPlannerActivity::where('user_id', $userId)->get();

        $events = [];
        foreach ($activities as $act) {
            // Colors matching Metronic Theme states (Warning, Primary, Success)
            $color = '#FFC700'; // warning (not started)
            if ($act->status == 'in progress') {
                $color = '#009EF7'; // primary (in progress)
            } elseif ($act->status == 'done') {
                $color = '#50CD89'; // success (done)
            }

            $events[] = [
                'id' => $act->id,
                'title' => $act->activity,
                'start' => $act->start_datetime,
                'end' => $act->end_datetime,
                'allDay' => false,
                'color' => $color,
                'textColor' => '#FFFFFF',
                'extendedProps' => [
                    'raw_start' => optional($act->start_datetime)->format('Y-m-d H:i'),
                    'raw_end'   => optional($act->end_datetime)->format('Y-m-d H:i'),
                    'activity'  => $act->activity,
                    'status'    => $act->status,
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Quick toggle activity completion status from list/dashboard checkbox.
     */
    public function quickToggleStatus(Request $request, $id)
    {
        $activity = DailyPlannerActivity::where('user_id', auth()->id())->findOrFail($id);

        $newStatus = $activity->status === 'done' ? 'not started' : 'done';
        $activity->update(['status' => $newStatus]);

        return response()->json([
            'success' => 'Status kegiatan berhasil diperbarui!',
            'status' => $newStatus
        ]);
    }

    /**
     * Show the Recurring Activity management page.
     */
    public function recurring()
    {
        $userId = auth()->id();
        $this->processRecurringActivities($userId);
        $recurrings = DailyPlannerRecurringActivity::where('user_id', $userId)->get();
        return view('pages.daily-planner.recurring', compact('recurrings'));
    }
    /**
     * Alias for route compatibility.
     */
    public function recurringActivity()
    {
        return $this->recurring();
    }

    /**
     * Store a newly created recurring activity.
     */
    public function storeRecurring(Request $request)
    {
        $request->validate([
            'activity' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'required_if:frequency,weekly|nullable|array',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);
        $data = $request->only([
            'activity',
            'frequency',
            'day_of_week',
            'day_of_month',
            'start_date',
            'end_date',
            'start_time',
            'duration_minutes',
            'is_active'
        ]);
        $data['user_id'] = auth()->id();
        
        // Ensure frequency-specific fields are correctly structured and nullified if not applicable
        if ($request->frequency === 'weekly') {
            $data['day_of_week'] = $request->day_of_week;
            $data['day_of_month'] = null;
        } elseif ($request->frequency === 'monthly') {
            $data['day_of_week'] = null;
        } else {
            $data['day_of_week'] = null;
            $data['day_of_month'] = null;
        }

        DailyPlannerRecurringActivity::create($data);
        return response()->json(['success' => 'Recurring activity created successfully.']);
    }

    /**
     * Update an existing recurring activity.
     */
    public function updateRecurring(Request $request, $id)
    {
        $request->validate([
            'activity' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'required_if:frequency,weekly|nullable|array',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $recurring = DailyPlannerRecurringActivity::where('user_id', auth()->id())->findOrFail($id);
        
        $data = $request->only([
            'activity',
            'frequency',
            'day_of_week',
            'day_of_month',
            'start_date',
            'end_date',
            'start_time',
            'duration_minutes',
            'is_active'
        ]);

        // Ensure frequency-specific fields are correctly structured and nullified if not applicable
        if ($request->frequency === 'weekly') {
            $data['day_of_week'] = $request->day_of_week;
            $data['day_of_month'] = null;
        } elseif ($request->frequency === 'monthly') {
            $data['day_of_week'] = null;
        } else {
            $data['day_of_week'] = null;
            $data['day_of_month'] = null;
        }

        $recurring->update($data);

        // Sync future instances: delete pending future instances from today onwards
        DailyPlannerActivity::where('recurring_activity_id', $recurring->id)
            ->where('recurring_date', '>=', Carbon::today()->toDateString())
            ->where('status', '!=', 'done')
            ->delete();

        // Regenerate if recurring activity is still active
        if ($recurring->is_active) {
            $this->processRecurringActivities(auth()->id());
        }

        return response()->json(['success' => 'Recurring activity updated successfully.']);
    }

    /**
     * Delete a recurring activity.
     */
    public function destroyRecurring($id)
    {
        $recurring = DailyPlannerRecurringActivity::where('user_id', auth()->id())->findOrFail($id);

        // Delete pending future instances from today onwards
        DailyPlannerActivity::where('recurring_activity_id', $recurring->id)
            ->where('recurring_date', '>=', Carbon::today()->toDateString())
            ->where('status', '!=', 'done')
            ->delete();

        $recurring->delete();
        return response()->json(['success' => 'Recurring activity deleted successfully.']);
    }

    /**
     * Manually trigger sync of recurring activities to daily activities.
     */
    public function syncRecurring()
    {
        $userId = auth()->id();
        $this->processRecurringActivities($userId);
        return response()->json(['success' => 'Recurring activities synced.']);
    }

    /**
     * Core engine: generate daily activities from active recurring definitions.
     * Performs a direct day-by-day scan over the next 14 days and inserts
     * missing entries — idempotent (safe to call on every page load).
     */
    protected function processRecurringActivities(int $userId): void
    {
        $recurrings = DailyPlannerRecurringActivity::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if ($recurrings->isEmpty()) {
            return;
        }

        $today   = Carbon::today();
        $endScan = $today->copy()->addDays(13); // inclusive 14-day window

        foreach ($recurrings as $rec) {
            $current = $today->copy();

            while ($current->lessThanOrEqualTo($endScan)) {
                // Check recurrence rule against this date
                if ($rec->matchesDate($current)) {
                    $alreadyExists = DailyPlannerActivity::where('user_id', $userId)
                        ->where('recurring_activity_id', $rec->id)
                        ->whereDate('recurring_date', $current->toDateString())
                        ->exists();

                    if (! $alreadyExists) {
                        $startDt = Carbon::parse($current->toDateString() . ' ' . $rec->start_time);
                        $endDt   = $startDt->copy()->addMinutes((int) $rec->duration_minutes);

                        DailyPlannerActivity::create([
                            'user_id'               => $userId,
                            'recurring_activity_id' => $rec->id,
                            'recurring_date'        => $current->toDateString(),
                            'activity'              => $rec->activity,
                            'status'                => 'not started',
                            'start_datetime'        => $startDt,
                            'end_datetime'          => $endDt,
                        ]);
                    }
                }

                $current->addDay();
            }
        }
    }
}
