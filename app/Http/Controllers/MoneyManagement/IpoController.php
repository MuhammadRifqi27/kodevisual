<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Exceptions\FinanceDomainException;
use App\Http\Controllers\Controller;
use App\Services\FinanceIpoOrder\FinanceIpoOrderService;
use App\Services\FinancePortfolio\FinancePortfolioService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class IpoController extends Controller
{
    public function __construct(
        private FinanceIpoOrderService $financeIpoOrderService,
        private FinancePortfolioService $financePortfolioService,
    ) {
    }

    public function index()
    {
        $stockPortfolios = $this->financePortfolioService->listForUser(auth()->id())
            ->filter(fn ($p) => $p->investment && $p->investment->type === 'stock')
            ->values();

        return view('pages.money-management.ipo.index', compact('stockPortfolios'));
    }

    public function datatable()
    {
        $orders = $this->financeIpoOrderService->listQuery(auth()->id())->get();

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('portfolio_name', function($row) {
                return $row->portfolio->account_name ?? '-';
            })
            ->editColumn('order_date', function($row) {
                return date('d M Y', strtotime($row->order_date));
            })
            ->editColumn('price_per_share', function($row) {
                return 'Rp ' . number_format($row->price_per_share, 0, ',', '.');
            })
            ->addColumn('status', function($row) {
                $badges = [
                    'pending' => 'warning',
                    'rejected' => 'danger',
                    'partial' => 'info',
                    'full' => 'success',
                ];
                return '<span class="badge badge-light-'.$badges[$row->status].'">'.ucfirst($row->status).'</span>';
            })
            ->addColumn('amount', function($row) {
                if ($row->status === 'pending') {
                    return '<span class="fw-bold">Rp ' . number_format($row->order_amount, 0, ',', '.') . '</span><br><span class="text-muted fs-8">Order (blocked)</span>';
                }
                return '<span class="fw-bold text-success">Rp ' . number_format($row->allotted_amount, 0, ',', '.') . '</span><br><span class="text-muted fs-8">Allotted, refund Rp ' . number_format($row->refund_amount, 0, ',', '.') . '</span>';
            })
            ->addColumn('action', function($row){
                $btn = '';
                if ($row->status === 'pending') {
                    $btn .= '<button data-id="'.$row->id.'"
                        data-finance_investment_id="'.$row->finance_investment_id.'"
                        data-asset="'.$row->asset.'"
                        data-order_date="'.$row->order_date.'"
                        data-price_per_share="'.$row->price_per_share.'"
                        data-lot_ordered="'.$row->lot_ordered.'"
                        data-description="'.e($row->description).'"
                        class="btn btn-icon btn-active-light-primary w-30px h-30px me-2 edit-ipo-btn" title="Edit"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
                    $btn .= '<button data-id="'.$row->id.'"
                        data-asset="'.$row->asset.'"
                        data-lot_ordered="'.$row->lot_ordered.'"
                        data-price_per_share="'.$row->price_per_share.'"
                        class="btn btn-icon btn-active-light-success w-30px h-30px me-2 allotment-ipo-btn" title="Confirm Allotment"><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
                }
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-ipo-btn" title="Delete"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->rawColumns(['status', 'amount', 'action'])
            ->make(true);
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

        $this->financeIpoOrderService->placeOrder(auth()->id(), $validated);

        return response()->json(['success' => 'Pesanan IPO berhasil dicatat']);
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
            $this->financeIpoOrderService->updateOrder(auth()->id(), $id, $validated);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['success' => 'Pesanan IPO berhasil diperbarui']);
    }

    public function confirmAllotment(Request $request, $id)
    {
        $validated = $request->validate([
            'lot_allotted' => 'required|integer|min:0',
            'allotment_date' => 'required|date',
        ]);

        try {
            $this->financeIpoOrderService->confirmAllotment(auth()->id(), $id, $validated);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['success' => 'Hasil penjatahan IPO berhasil dicatat']);
    }

    public function destroy($id)
    {
        $this->financeIpoOrderService->cancelOrder(auth()->id(), $id);

        return response()->json(['success' => 'Pesanan IPO berhasil dihapus']);
    }
}
