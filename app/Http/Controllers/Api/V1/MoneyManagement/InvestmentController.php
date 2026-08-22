<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestment;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function index()
    {
        return response()->json(FinanceInvestment::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:crypto,stock,other',
            'description' => 'nullable|string',
        ]);

        $investment = FinanceInvestment::create($request->only('name', 'code', 'type', 'description'));

        return response()->json($investment, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:crypto,stock,other',
            'description' => 'nullable|string',
        ]);

        $investment = FinanceInvestment::findOrFail($id);
        $investment->update($request->only('name', 'code', 'type', 'description'));

        return response()->json($investment);
    }

    public function destroy($id)
    {
        FinanceInvestment::findOrFail($id)->delete();

        return response()->json(['success' => 'Data investasi berhasil dihapus']);
    }
}
