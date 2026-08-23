<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceTransfer\FinanceTransferService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(private FinanceTransferService $financeTransferService)
    {
    }

    /**
     * Each transfer is stored as a pair of finance_transactions rows (out + in).
     * Only the outbound (negative-amount) leg is listed here to avoid duplicate rows;
     * the inbound leg is included as "destination".
     */
    public function index(Request $request)
    {
        $query = $this->financeTransferService->listQuery(auth()->id());

        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $outbound = $this->financeTransferService->transfer(auth()->id(), $validated);

        return response()->json($outbound->load(['portfolio', 'destinationPortfolio']), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $outbound = $this->financeTransferService->updateTransfer(auth()->id(), $id, $validated);

        return response()->json($outbound->load(['portfolio', 'destinationPortfolio']));
    }

    public function destroy($id)
    {
        $this->financeTransferService->deleteTransfer(auth()->id(), $id);

        return response()->json(['success' => 'Transfer berhasil dihapus']);
    }
}
