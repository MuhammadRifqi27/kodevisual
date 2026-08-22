<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use App\Models\TravelTrip;
use Illuminate\Http\Request;

class TravelDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $trips = TravelTrip::with('expenses')->where('user_id', $userId)->orderBy('start_date', 'desc')->get();
        
        $totalExpenses = 0;
        foreach($trips as $trip) {
            $totalExpenses += $trip->expenses->sum('amount');
        }

        $upcomingTripsCount = TravelTrip::where('user_id', $userId)
            ->where('start_date', '>', now())
            ->count();

        return view('pages.travel.dashboard', compact('trips', 'totalExpenses', 'upcomingTripsCount'));
    }
}
