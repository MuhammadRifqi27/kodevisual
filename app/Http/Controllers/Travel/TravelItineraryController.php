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
            'trip_id'        => 'required|exists:travel_trips,id',
            'datetime'       => 'required|string', // Format: YYYY-MM-DD HH:mm
            'activity'       => 'required|string|max:255',
            'location'       => 'nullable|string|max:255',
            'cost_type'      => 'required|in:total,per_person',
            'cost_estimate'  => 'nullable|numeric|min:0',
            'cost_per_person' => 'nullable|numeric|min:0',
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
        $startDate = Carbon::parse($trip->start_date)->startOfDay();
        $currentDate = $dt->copy()->startOfDay();
        $validated['day_number'] = $startDate->diffInDays($currentDate) + 1;

        // Calculate cost based on cost_type
        $persons = max($trip->number_of_persons, 1);

        if ($validated['cost_type'] === 'total') {
            // User entered total cost → derive per-person
            $validated['cost_estimate'] = $validated['cost_estimate'] ?? 0;
            $validated['cost_per_person'] = $validated['cost_estimate'] / $persons;
        } else {
            // User entered per-person cost → derive total
            $validated['cost_per_person'] = $validated['cost_per_person'] ?? 0;
            $validated['cost_estimate'] = $validated['cost_per_person'] * $persons;
        }

        TravelItinerary::create($validated);

        return redirect()->back()->with('success', 'Activity added!');
    }

    public function update(Request $request, $id)
    {
        $item = TravelItinerary::findOrFail($id);
        $trip = $item->trip;

        $validated = $request->validate([
            'datetime'        => 'required|string',
            'activity'        => 'required|string|max:255',
            'location'        => 'nullable|string|max:255',
            'cost_type'       => 'required|in:total,per_person',
            'cost_estimate'   => 'nullable|numeric|min:0',
            'cost_per_person' => 'nullable|numeric|min:0',
        ]);

        $dt = Carbon::parse($validated['datetime']);

        // Ensure date is within trip range
        $tripStart = Carbon::parse($trip->start_date)->startOfDay();
        $tripEnd = Carbon::parse($trip->end_date)->endOfDay();

        if ($dt->lt($tripStart) || $dt->gt($tripEnd)) {
            return redirect()->back()->withErrors(['datetime' => 'Activity date must be between ' . $trip->start_date . ' and ' . $trip->end_date]);
        }

        $validated['date'] = $dt->toDateString();
        $validated['time'] = $dt->toTimeString();

        // Recalculate day number
        $startDate = Carbon::parse($trip->start_date)->startOfDay();
        $currentDate = $dt->copy()->startOfDay();
        $validated['day_number'] = $startDate->diffInDays($currentDate) + 1;

        // Calculate cost based on cost_type
        $persons = max($trip->number_of_persons, 1);

        if ($validated['cost_type'] === 'total') {
            $validated['cost_estimate'] = $validated['cost_estimate'] ?? 0;
            $validated['cost_per_person'] = $validated['cost_estimate'] / $persons;
        } else {
            $validated['cost_per_person'] = $validated['cost_per_person'] ?? 0;
            $validated['cost_estimate'] = $validated['cost_per_person'] * $persons;
        }

        $item->update($validated);

        return redirect()->back()->with('success', 'Activity updated!');
    }

    public function destroy($id)
    {
        $item = TravelItinerary::findOrFail($id);
        $item->delete();
        return redirect()->back()->with('success', 'Activity removed!');
    }
}
