<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use App\Models\TravelExpense;
use App\Models\TravelTrip;
use Illuminate\Http\Request;

class TravelExpenseController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $expenses = TravelExpense::whereHas('trip', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('trip')->orderBy('date', 'desc')->get();
        
        return view('pages.travel.expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:travel_trips,id',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        TravelExpense::create($validated);

        return redirect()->back()->with('success', 'Expense recorded!');
    }

    public function destroy($id)
    {
        $expense = TravelExpense::findOrFail($id);
        $expense->delete();
        return redirect()->back()->with('success', 'Expense record deleted!');
    }
}
