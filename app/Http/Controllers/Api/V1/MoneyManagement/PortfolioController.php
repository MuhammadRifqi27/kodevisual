<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Exceptions\FinanceDomainException;
use App\Http\Controllers\Controller;
use App\Services\FinancePortfolio\FinancePortfolioService;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function __construct(private FinancePortfolioService $financePortfolioService)
    {
    }

    public function index()
    {
        return response()->json($this->financePortfolioService->listForUser(auth()->id()));
    }

    public function show($id)
    {
        $portfolio = $this->financePortfolioService->assertOwned(auth()->id(), $id)->load('investment');

        return response()->json($portfolio);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'finance_investment_id' => 'required|exists:finance_investments,id',
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'account_investment' => 'nullable|boolean',
        ]);

        $portfolio = $this->financePortfolioService->createPortfolio(auth()->id(), $validated);

        return response()->json($portfolio->load('investment'), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'finance_investment_id' => 'required|exists:finance_investments,id',
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'account_investment' => 'nullable|boolean',
        ]);

        $portfolio = $this->financePortfolioService->updatePortfolio(auth()->id(), $id, $validated);

        return response()->json($portfolio->load('investment'));
    }

    public function destroy($id)
    {
        try {
            $this->financePortfolioService->deletePortfolio(auth()->id(), $id);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['success' => 'Akun Portofolio berhasil dihapus']);
    }

    /**
     * Unified activity feed for one portfolio: its internal investment ledger
     * (deposit/withdrawal/profit/loss) merged with general income/expense/transfer
     * transactions linked to it.
     */
    public function transactions($id)
    {
        $data = $this->financePortfolioService->activityFeed(auth()->id(), $id);

        return response()->json($data);
    }

    public function storeTransaction(Request $request, $id)
    {
        $validated = $request->validate([
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
            'date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal,profit,loss',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $transaction = $this->financePortfolioService->createLedgerEntry(auth()->id(), $id, $validated);

        return response()->json($transaction, 201);
    }

    public function updateTransaction(Request $request, $id, $trxId)
    {
        $validated = $request->validate([
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
            'date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal,profit,loss',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        try {
            $trx = $this->financePortfolioService->updateLedgerEntry(auth()->id(), $trxId, $validated, (int) $id);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($trx);
    }

    public function destroyTransaction($id, $trxId)
    {
        try {
            $this->financePortfolioService->deleteLedgerEntry(auth()->id(), $trxId, (int) $id);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
