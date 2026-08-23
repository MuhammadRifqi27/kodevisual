<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Exceptions\FinanceDomainException;
use App\Http\Controllers\Controller;
use App\Services\FinanceIpoOrder\FinanceIpoOrderService;
use Illuminate\Http\Request;

class IpoController extends Controller
{
    public function __construct(private FinanceIpoOrderService $financeIpoOrderService)
    {
    }

    public function index()
    {
        $orders = $this->financeIpoOrderService->listQuery(auth()->id())->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'order_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot_ordered' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $order = $this->financeIpoOrderService->placeOrder(auth()->id(), $validated);

        return response()->json($order->load('portfolio'), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'order_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot_ordered' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        try {
            $order = $this->financeIpoOrderService->updateOrder(auth()->id(), $id, $validated);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($order->load('portfolio'));
    }

    public function confirmAllotment(Request $request, $id)
    {
        $validated = $request->validate([
            'lot_allotted' => 'required|integer|min:0',
            'allotment_date' => 'required|date',
        ]);

        try {
            $order = $this->financeIpoOrderService->confirmAllotment(auth()->id(), $id, $validated);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json($order->load('portfolio'));
    }

    public function destroy($id)
    {
        $this->financeIpoOrderService->cancelOrder(auth()->id(), $id);

        return response()->json(['success' => 'Pesanan IPO berhasil dihapus']);
    }
}
