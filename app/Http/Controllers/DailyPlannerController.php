<?php

namespace App\Http\Controllers;

use App\Models\DailyPlannerActivity;
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
}

