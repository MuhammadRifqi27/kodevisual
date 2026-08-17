<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use App\Models\TravelBudget;
use App\Models\TravelTrip;
use Illuminate\Http\Request;

class TravelBudgetController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $trips = TravelTrip::with('budgets')->where('user_id', $userId)->get();
        return view('pages.travel.budgets.index', compact('trips'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:travel_trips,id',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $trip = TravelTrip::with('budgets')->findOrFail($validated['trip_id']);
        
        $currentAllocated = $trip->budgets->sum('amount');
        if (($currentAllocated + $validated['amount']) > $trip->total_budget) {
            return redirect()->back()->with('warning', 'Calculation Error: This allocation would exceed your mission budget of ' . $trip->currency . ' ' . number_format($trip->total_budget));
        }

        TravelBudget::create($validated);

        return redirect()->back()->with('success', 'Budget allocated successfully!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $budget = TravelBudget::findOrFail($id);
        $trip = TravelTrip::with('budgets')->findOrFail($budget->trip_id);

        $currentAllocated = $trip->budgets->where('id', '!=', $budget->id)->sum('amount');
        if (($currentAllocated + $validated['amount']) > $trip->total_budget) {
            return redirect()->back()->with('warning', 'Calculation Error: This allocation would exceed your mission budget of ' . $trip->currency . ' ' . number_format($trip->total_budget));
        }

        $budget->update($validated);

        return redirect()->back()->with('success', 'Budget allocation updated!');
    }

    public function destroy($id)
    {
        $budget = TravelBudget::findOrFail($id);
        $budget->delete();
        return redirect()->back()->with('success', 'Allocation removed!');
    }
}
