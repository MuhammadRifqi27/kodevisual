<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceIpoOrder;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinancePortfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IpoController extends Controller
{
    public function index()
    {
        $stockPortfolios = FinancePortfolio::where('user_id', auth()->id())
            ->whereHas('investment', function($q) {
                $q->where('type', 'stock');
            })
            ->get();

        return view('pages.money-management.ipo.index', compact('stockPortfolios'));
    }

    public function datatable()
    {
        $orders = FinanceIpoOrder::where('user_id', auth()->id())
            ->with('portfolio')
            ->orderBy('order_date', 'desc')
            ->get();

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
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'order_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot_ordered' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $userId = auth()->id();
        $portfolio = FinancePortfolio::where('user_id', $userId)
            ->whereHas('investment', fn($q) => $q->where('type', 'stock'))
            ->findOrFail($request->finance_investment_id);

        $orderAmount = $request->lot_ordered * 100 * $request->price_per_share;

        DB::transaction(function() use ($request, $userId, $orderAmount) {
            $order = FinanceIpoOrder::create([
                'user_id' => $userId,
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'order_date' => $request->order_date,
                'price_per_share' => $request->price_per_share,
                'lot_ordered' => $request->lot_ordered,
                'description' => $request->description,
            ]);

            $orderTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'date' => $request->order_date,
                'type' => 'withdrawal',
                'amount' => $orderAmount,
                'description' => 'IPO Order: ' . $request->asset . ' - ' . $request->lot_ordered . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.') . ' (pending allotment)',
            ]);

            $order->update(['order_transaction_id' => $orderTrx->id]);
        });

        return response()->json(['success' => 'Pesanan IPO berhasil dicatat']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'order_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot_ordered' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $order = FinanceIpoOrder::where('user_id', auth()->id())->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Pesanan yang sudah ada hasil penjatahan tidak bisa diedit. Hapus lalu buat ulang jika perlu koreksi.'], 400);
        }

        $portfolio = FinancePortfolio::where('user_id', auth()->id())
            ->whereHas('investment', fn($q) => $q->where('type', 'stock'))
            ->findOrFail($request->finance_investment_id);

        $orderAmount = $request->lot_ordered * 100 * $request->price_per_share;

        DB::transaction(function() use ($request, $order, $orderAmount) {
            $order->update([
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'order_date' => $request->order_date,
                'price_per_share' => $request->price_per_share,
                'lot_ordered' => $request->lot_ordered,
                'description' => $request->description,
            ]);

            $order->orderTransaction()->update([
                'finance_investment_id' => $request->finance_investment_id,
                'asset' => $request->asset,
                'date' => $request->order_date,
                'amount' => $orderAmount,
                'description' => 'IPO Order: ' . $request->asset . ' - ' . $request->lot_ordered . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.') . ' (pending allotment)',
            ]);
        });

        return response()->json(['success' => 'Pesanan IPO berhasil diperbarui']);
    }

    public function confirmAllotment(Request $request, $id)
    {
        $request->validate([
            'lot_allotted' => 'required|integer|min:0',
            'allotment_date' => 'required|date',
        ]);

        $order = FinanceIpoOrder::where('user_id', auth()->id())->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Pesanan ini sudah punya hasil penjatahan'], 400);
        }

        if ($request->lot_allotted > $order->lot_ordered) {
            return response()->json(['error' => 'Lot allotted tidak boleh lebih besar dari lot yang dipesan'], 400);
        }

        $userId = auth()->id();
        $orderAmount = $order->order_amount;
        $allottedAmount = $request->lot_allotted * 100 * $order->price_per_share;

        DB::transaction(function() use ($request, $order, $userId, $orderAmount, $allottedAmount) {
            $releaseTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $order->finance_investment_id,
                'asset' => $order->asset,
                'date' => $request->allotment_date,
                'type' => 'deposit',
                'amount' => $orderAmount,
                'description' => 'IPO Block Released: ' . $order->asset,
            ]);

            $holdingTrxId = null;
            $offsetTrxId = null;

            if ($request->lot_allotted > 0) {
                $holdingTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $order->finance_investment_id,
                    'asset' => $order->asset,
                    'lot' => $request->lot_allotted,
                    'date' => $request->allotment_date,
                    'type' => 'deposit',
                    'amount' => $allottedAmount,
                    'description' => 'IPO Allotment: ' . $order->asset . ' - ' . $request->lot_allotted . '/' . $order->lot_ordered . ' lot @ Rp ' . number_format($order->price_per_share, 0, ',', '.'),
                ]);
                $holdingTrxId = $holdingTrx->id;

                $offsetTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $order->finance_investment_id,
                    'date' => $request->allotment_date,
                    'type' => 'withdrawal',
                    'amount' => $allottedAmount,
                    'description' => 'IPO Allotment funded from broker balance: ' . $order->asset,
                ]);
                $offsetTrxId = $offsetTrx->id;
            }

            $order->update([
                'lot_allotted' => $request->lot_allotted,
                'allotment_date' => $request->allotment_date,
                'release_transaction_id' => $releaseTrx->id,
                'holding_transaction_id' => $holdingTrxId,
                'offset_transaction_id' => $offsetTrxId,
            ]);
        });

        return response()->json(['success' => 'Hasil penjatahan IPO berhasil dicatat']);
    }

    public function destroy($id)
    {
        $order = FinanceIpoOrder::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function() use ($order) {
            FinanceInvestmentTransaction::whereIn('id', array_filter([
                $order->order_transaction_id,
                $order->release_transaction_id,
                $order->holding_transaction_id,
                $order->offset_transaction_id,
            ]))->delete();

            $order->delete();
        });

        return response()->json(['success' => 'Pesanan IPO berhasil dihapus']);
    }
}
