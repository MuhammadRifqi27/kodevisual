<?php

namespace App\Http\Controllers;

use App\Services\WeddingPlanner\WeddingPlannerService;
use Illuminate\Http\Request;

class WeddingPlannerController extends Controller
{
    public function __construct(private WeddingPlannerService $weddingPlannerService)
    {
    }

    public function index()
    {
        $overview = $this->weddingPlannerService->overview(auth()->id());

        return view('pages.money-management.wedding-planner.index', $overview);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_amount' => 'required|numeric|min:1000000',
            'target_years' => 'required|integer|min:1|max:10',
        ]);

        $this->weddingPlannerService->updateTarget(auth()->id(), $validated);

        return response()->json(['success' => 'Target pernikahan berhasil disimpan dan item biaya diupdate proporsional!']);
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'estimated_amount' => 'required|numeric|min:0',
        ]);

        $this->weddingPlannerService->addItem(auth()->id(), $validated);

        return response()->json(['success' => 'Item berhasil ditambahkan!']);
    }

    public function destroyItem($id)
    {
        $this->weddingPlannerService->removeItem(auth()->id(), $id);

        return response()->json(['success' => 'Item berhasil dihapus!']);
    }

    public function storeSavings(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $this->weddingPlannerService->addSavings(auth()->id(), $validated);

        return response()->json(['success' => 'Transaksi tabungan berhasil ditambahkan!']);
    }

    public function destroySavings($id)
    {
        $this->weddingPlannerService->removeSavings(auth()->id(), $id);

        return response()->json(['success' => 'Transaksi tabungan berhasil dihapus!']);
    }
}
