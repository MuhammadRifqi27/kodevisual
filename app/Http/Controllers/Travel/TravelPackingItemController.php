<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use App\Models\TravelPackingItem;
use Illuminate\Http\Request;

class TravelPackingItemController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:travel_trips,id',
            'category' => 'nullable|string',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        TravelPackingItem::create($validated);

        return redirect()->back()->with('success', 'Packing item added!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'nullable|string',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $item = TravelPackingItem::findOrFail($id);
        $item->update($validated);

        return redirect()->back()->with('success', 'Packing item updated!');
    }

    public function togglePacked($id)
    {
        $item = TravelPackingItem::findOrFail($id);
        $item->update(['is_packed' => !$item->is_packed]);

        return response()->json(['success' => true, 'is_packed' => $item->is_packed]);
    }

    public function destroy($id)
    {
        $item = TravelPackingItem::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Packing item removed!');
    }
}
