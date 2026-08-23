<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceInvestment\FinanceInvestmentService;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function __construct(private FinanceInvestmentService $financeInvestmentService)
    {
    }

    public function index()
    {
        return response()->json($this->financeInvestmentService->query()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:crypto,stock,other',
            'description' => 'nullable|string',
        ]);

        $investment = $this->financeInvestmentService->create($validated);

        return response()->json($investment, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:crypto,stock,other',
            'description' => 'nullable|string',
        ]);

        $investment = $this->financeInvestmentService->update($id, $validated);

        return response()->json($investment);
    }

    public function destroy($id)
    {
        $this->financeInvestmentService->delete($id);

        return response()->json(['success' => 'Data investasi berhasil dihapus']);
    }
}
