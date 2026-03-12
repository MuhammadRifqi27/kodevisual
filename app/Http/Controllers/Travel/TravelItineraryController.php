<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use App\Models\TravelItinerary;
use App\Models\TravelTrip;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TravelItineraryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:travel_trips,id',
            'datetime' => 'required|string', // Format: YYYY-MM-DD HH:mm
            'activity' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'cost_estimate' => 'nullable|numeric',
        ]);

        $trip = TravelTrip::findOrFail($validated['trip_id']);
        
        $dt = Carbon::parse($validated['datetime']);
        
        // Ensure date is within trip range
        $tripStart = Carbon::parse($trip->start_date)->startOfDay();
        $tripEnd = Carbon::parse($trip->end_date)->endOfDay();
        
        if ($dt->lt($tripStart) || $dt->gt($tripEnd)) {
            return redirect()->back()->withErrors(['datetime' => 'Activity date must be between ' . $trip->start_date . ' and ' . $trip->end_date]);
        }

        $validated['date'] = $dt->toDateString();
        $validated['time'] = $dt->toTimeString();

        // Calculate day number based on trip start date
        // diffInDays gives the absolute difference, so if same day it's 0. We want Day 1.
        $startDate = Carbon::parse($trip->start_date)->startOfDay();
        $currentDate = $dt->copy()->startOfDay();
        $validated['day_number'] = $startDate->diffInDays($currentDate) + 1;

        TravelItinerary::create($validated);

        return redirect()->back()->with('success', 'Activity added!');
    }

    public function destroy($id)
    {
        $item = TravelItinerary::findOrFail($id);
        $item->delete();
        return redirect()->back()->with('success', 'Activity removed!');
    }
}
