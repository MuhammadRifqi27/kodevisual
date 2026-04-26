<?php

namespace App\Http\Controllers;

use App\Models\WeddingPlan;
use App\Models\WeddingPlannerItem;
use App\Models\WeddingSavingsTransaction;
use App\Models\FinancePortfolio;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WeddingPlannerController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $plan = WeddingPlan::firstOrCreate(
            ['user_id' => $userId],
            ['target_amount' => 50000000, 'target_years' => 3]
        );
        
        $items = WeddingPlannerItem::where('user_id', $userId)->get();
        $savings = WeddingSavingsTransaction::where('user_id', $userId)->orderBy('date', 'desc')->get();
        $totalSavedManual = $savings->sum('amount');

        // BNI Portfolio Connection
        $bniPortfolio = FinancePortfolio::where('user_id', $userId)
            ->where('account_name', 'like', '%Bank BNI%')
            ->first();
        $bniBalance = $bniPortfolio ? $bniPortfolio->balance : 0;
        
        // Seed default items if none exist
        if ($items->isEmpty()) {
            $target = $plan->target_amount;
            $defaults = [
                ['name' => 'Venue & Catering', 'percent' => 45],
                ['name' => 'Decoration & Florist', 'percent' => 15],
                ['name' => 'Photo & Videography', 'percent' => 10],
                ['name' => 'Makeup & Attire', 'percent' => 10],
                ['name' => 'Invitation & Souvenir', 'percent' => 5],
                ['name' => 'Emergency Fund', 'percent' => 15],
            ];
            
            foreach ($defaults as $d) {
                WeddingPlannerItem::create([
                    'user_id' => $userId,
                    'name' => $d['name'],
                    'estimated_amount' => ($d['percent'] / 100) * $target,
                    'status' => 'planning'
                ]);
            }
            $items = WeddingPlannerItem::where('user_id', $userId)->get();
        }

        return view('pages.money-management.wedding-planner.index', compact('plan', 'items', 'savings', 'totalSavedManual', 'bniBalance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_amount' => 'required|numeric|min:1000000',
            'target_years' => 'required|integer|min:1|max:10',
        ]);

        $userId = auth()->id();
        $plan = WeddingPlan::where('user_id', $userId)->first();
        
        $oldTarget = $plan ? $plan->target_amount : 50000000;
        $newTarget = floatval($request->target_amount);
        $factor = ($oldTarget > 0) ? ($newTarget / $oldTarget) : 1;

        // Update target
        WeddingPlan::updateOrCreate(
            ['user_id' => $userId],
            [
                'target_amount' => $newTarget,
                'target_years' => $request->target_years
            ]
        );

        // Recalculate all items proportionally
        $items = WeddingPlannerItem::where('user_id', $userId)->get();
        foreach ($items as $item) {
            $item->update([
                'estimated_amount' => $item->estimated_amount * $factor
            ]);
        }

        return response()->json(['success' => 'Target pernikahan berhasil disimpan dan item biaya diupdate proporsional!']);
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'estimated_amount' => 'required|numeric|min:0',
        ]);

        WeddingPlannerItem::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'estimated_amount' => $request->estimated_amount,
            'status' => 'planning'
        ]);

        return response()->json(['success' => 'Item berhasil ditambahkan!']);
    }

    public function destroyItem($id)
    {
        WeddingPlannerItem::where('user_id', auth()->id())->findOrFail($id)->delete();
        return response()->json(['success' => 'Item berhasil dihapus!']);
    }

    // New Savings Transaction Methods
    public function storeSavings(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        WeddingSavingsTransaction::create([
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return response()->json(['success' => 'Transaksi tabungan berhasil ditambahkan!']);
    }

    public function destroySavings($id)
    {
        WeddingSavingsTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();
        return response()->json(['success' => 'Transaksi tabungan berhasil dihapus!']);
    }
}
