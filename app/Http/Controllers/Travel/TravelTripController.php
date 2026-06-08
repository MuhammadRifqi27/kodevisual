<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use App\Models\TravelTrip;
use App\Models\TravelTripImage;
use Illuminate\Http\Request;

class TravelTripController extends Controller
{
    public function index()
    {
        $trips = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries', 'budgets', 'expenses', 'coverImage'])
            ->orderBy('start_date', 'desc')
            ->get();
        return view('pages.travel.trips.index', compact('trips'));
    }

    public function create()
    {
        return view('pages.travel.trips.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'destination'       => 'required|string|max:255',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'total_budget'      => 'required|numeric|min:0',
            'currency'          => 'required|string|max:10',
            'number_of_persons' => 'required|integer|min:1',
            'cover_image'       => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'planned';
        unset($validated['cover_image']); // simpan di tabel terpisah

        $trip = TravelTrip::create($validated);

        // Simpan gambar ke tabel travel_trip_images
        if ($request->hasFile('cover_image')) {
            $file     = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = 'assets/media/icon-travel/' . $filename;
            $file->move(public_path('assets/media/icon-travel'), $filename);

            TravelTripImage::create([
                'trip_id'  => $trip->id,
                'filename' => $filename,
                'path'     => $path,
                'url'      => asset($path),
                'is_cover' => true,
            ]);
        }

        return redirect()->route('travel.trips.index')->with('success', 'Trip created successfully!');
    }

    public function show($id)
    {
        $trip = TravelTrip::with(['itineraries', 'budgets', 'expenses', 'coverImage'])->findOrFail($id);
        return view('pages.travel.trips.show', compact('trip'));
    }

    public function edit($id)
    {
        $trip = TravelTrip::with('coverImage')->findOrFail($id);
        return view('pages.travel.trips.edit', compact('trip'));
    }

    public function update(Request $request, $id)
    {
        $trip = TravelTrip::findOrFail($id);
        $oldPersons = $trip->number_of_persons;

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'destination'       => 'required|string|max:255',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'total_budget'      => 'required|numeric|min:0',
            'currency'          => 'required|string|max:10',
            'number_of_persons' => 'required|integer|min:1',
            'status'            => 'required|in:draft,planned,ongoing,completed,cancelled',
            'cover_image'       => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        unset($validated['cover_image']);
        $trip->update($validated);

        // If number_of_persons changed, recalculate itineraries
        if ($oldPersons != $trip->number_of_persons) {
            $newPersons = max($trip->number_of_persons, 1);
            foreach ($trip->itineraries as $itinerary) {
                if ($itinerary->cost_type === 'total') {
                    $itinerary->cost_per_person = $itinerary->cost_estimate / $newPersons;
                } else {
                    $itinerary->cost_estimate = $itinerary->cost_per_person * $newPersons;
                }
                $itinerary->save();
            }
        }

        // Ganti cover image jika ada upload baru
        if ($request->hasFile('cover_image')) {
            // Hapus cover lama dari disk & DB
            $oldCover = TravelTripImage::where('trip_id', $trip->id)->where('is_cover', true)->first();
            if ($oldCover) {
                $oldFullPath = public_path($oldCover->path);
                if (file_exists($oldFullPath)) {
                    unlink($oldFullPath);
                }
                $oldCover->delete();
            }

            $file     = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = 'assets/media/icon-travel/' . $filename;
            $file->move(public_path('assets/media/icon-travel'), $filename);

            TravelTripImage::create([
                'trip_id'  => $trip->id,
                'filename' => $filename,
                'path'     => $path,
                'url'      => asset($path),
                'is_cover' => true,
            ]);
        }

        return redirect()->route('travel.trips.index')->with('success', 'Trip updated successfully!');
    }

    public function destroy($id)
    {
        $trip = TravelTrip::with('images')->findOrFail($id);

        // Hapus semua file gambar dari disk
        foreach ($trip->images as $image) {
            $fullPath = public_path($image->path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $trip->delete();
        return redirect()->route('travel.trips.index')->with('success', 'Trip deleted successfully!');
    }

    public function exportAll()
    {
        $trips = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries', 'budgets', 'expenses'])
            ->orderBy('start_date', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="all_trips_export_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($trips) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Title', 'Destination', 'Start Date', 'End Date', 
                'Total Budget', 'Currency', 'Number of Persons', 'Status', 
                'Total Allocated', 'Total Spent', 'Remaining Budget'
            ]);

            foreach ($trips as $trip) {
                $allocated = $trip->budgets->sum('amount');
                $spent = $trip->expenses->sum('amount');
                $remaining = $trip->total_budget - $spent;

                fputcsv($file, [
                    $trip->id,
                    $trip->title,
                    $trip->destination,
                    $trip->start_date,
                    $trip->end_date,
                    $trip->total_budget,
                    $trip->currency,
                    $trip->number_of_persons,
                    ucfirst($trip->status),
                    $allocated,
                    $spent,
                    $remaining
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportFull($id)
    {
        $trip = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries', 'budgets', 'expenses'])
            ->findOrFail($id);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="trip_' . str_replace(' ', '_', $trip->title) . '_full_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($trip) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Section 1: Trip Summary
            fputcsv($file, ['EXPEDITION DETAILED SUMMARY']);
            fputcsv($file, []);
            fputcsv($file, ['ID', 'Title', 'Destination', 'Start Date', 'End Date', 'Total Budget', 'Currency', 'Number of Persons', 'Status']);
            fputcsv($file, [
                $trip->id,
                $trip->title,
                $trip->destination,
                $trip->start_date,
                $trip->end_date,
                $trip->total_budget,
                $trip->currency,
                $trip->number_of_persons,
                ucfirst($trip->status)
            ]);
            fputcsv($file, []);
            fputcsv($file, []);

            // Section 2: Itinerary
            fputcsv($file, ['ITINERARY SCHEDULE']);
            fputcsv($file, []);
            fputcsv($file, ['Day', 'Date', 'Time', 'Activity', 'Location', 'Notes', 'Total Cost Estimate (' . $trip->currency . ')', 'Price Per Person (' . $trip->currency . ')']);
            foreach ($trip->itineraries->sortBy(['day_number', 'time']) as $item) {
                fputcsv($file, [
                    'Day ' . $item->day_number,
                    $item->date,
                    $item->time ? substr($item->time, 0, 5) : '--:--',
                    $item->activity,
                    $item->location,
                    $item->notes,
                    $item->cost_estimate,
                    $item->cost_per_person
                ]);
            }
            fputcsv($file, []);
            fputcsv($file, []);

            // Section 3: Budget Allocations
            fputcsv($file, ['BUDGET ALLOCATIONS']);
            fputcsv($file, []);
            fputcsv($file, ['Category', 'Amount Allocated (' . $trip->currency . ')', 'Notes']);
            foreach ($trip->budgets as $budget) {
                fputcsv($file, [
                    $budget->category,
                    $budget->amount,
                    $budget->notes
                ]);
            }
            fputcsv($file, []);
            fputcsv($file, []);

            // Section 4: Expenses Feed
            fputcsv($file, ['EXPENSES RECORDED']);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Category', 'Description', 'Amount Spent (' . $trip->currency . ')', 'Pre-Trip?']);
            foreach ($trip->expenses->sortBy('date') as $expense) {
                fputcsv($file, [
                    $expense->date,
                    $expense->category,
                    $expense->description,
                    $expense->amount,
                    $expense->is_pre_trip ? 'Yes' : 'No'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportItinerary($id)
    {
        $trip = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries'])
            ->findOrFail($id);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="trip_' . str_replace(' ', '_', $trip->title) . '_itinerary_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($trip) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ITINERARY SCHEDULE FOR ' . strtoupper($trip->title)]);
            fputcsv($file, ['Destination: ' . $trip->destination]);
            fputcsv($file, ['Participants: ' . $trip->number_of_persons . ' person(s)']);
            fputcsv($file, []);

            fputcsv($file, ['Day', 'Date', 'Time', 'Activity', 'Location', 'Notes', 'Total Cost Estimate (' . $trip->currency . ')', 'Cost Per Person (' . $trip->currency . ')']);
            
            $totalCost = 0;
            $totalCostPerPerson = 0;
            foreach ($trip->itineraries->sortBy(['day_number', 'time']) as $item) {
                $totalCost += $item->cost_estimate;
                $totalCostPerPerson += $item->cost_per_person;
                fputcsv($file, [
                    'Day ' . $item->day_number,
                    $item->date,
                    $item->time ? substr($item->time, 0, 5) : '--:--',
                    $item->activity,
                    $item->location,
                    $item->notes,
                    $item->cost_estimate,
                    $item->cost_per_person
                ]);
            }
            
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', '', '', '', '', $totalCost, $totalCostPerPerson]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
