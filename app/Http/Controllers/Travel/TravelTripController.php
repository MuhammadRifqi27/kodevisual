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
            'title'        => 'required|string|max:255',
            'destination'  => 'required|string|max:255',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'total_budget' => 'required|numeric|min:0',
            'currency'     => 'required|string|max:10',
            'cover_image'  => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
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

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'destination'  => 'required|string|max:255',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'total_budget' => 'required|numeric|min:0',
            'currency'     => 'required|string|max:10',
            'status'       => 'required|in:draft,planned,ongoing,completed,cancelled',
            'cover_image'  => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        unset($validated['cover_image']);
        $trip->update($validated);

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
}
