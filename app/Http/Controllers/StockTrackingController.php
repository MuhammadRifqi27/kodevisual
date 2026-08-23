<?php

namespace App\Http\Controllers;

use App\Exceptions\FinanceDomainException;
use App\Services\FinanceStockTracking\FinanceStockTrackingService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StockTrackingController extends Controller
{
    public function __construct(private FinanceStockTrackingService $financeStockTrackingService)
    {
    }

    public function index()
    {
        $overview = $this->financeStockTrackingService->overview(auth()->id());

        return view('pages.money-management.stock-tracking.index', [
            'stockPortfolios' => $overview['stockPortfolios'],
            'totalStockValue' => $overview['totalStockValue'],
            'emitenBalances' => $overview['emitenBalances'],
            'tradingBalance' => $overview['tradingBalance'],
            'openAmount' => $overview['openAmount'],
            'investedTotal' => $overview['investedTotal'],
            'totalPnl' => $overview['totalPnl'],
            'totalEquity' => $overview['totalEquity'],
        ]);
    }

    public function datatable()
    {
        $feed = $this->financeStockTrackingService->activityFeed(auth()->id());
        $data = $feed['data'];
        $ipoTrxIds = $feed['ipoTrxIds'];
        $tradeTrxIds = $feed['tradeTrxIds'];

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('date', function($row) {
                return date('d M Y', strtotime($row->date));
            })
            ->addColumn('portfolio_name', function($row) {
                return $row->portfolio->account_name ?? '-';
            })
            ->editColumn('asset', function($row) {
                return $row->asset ? '<span class="badge badge-light-info">'.$row->asset.'</span>' : '<span class="text-muted">-</span>';
            })
            ->editColumn('lot', function($row) {
                if ($row->lot === null) {
                    return '<span class="text-muted">-</span>';
                }
                if ($row->source_type === 'transfer') {
                    return ($row->lot < 0 ? '- ' : '+ ') . abs($row->lot) . ' Lot';
                }
                $isIn = in_array($row->type, ['deposit', 'profit']);
                return ($isIn ? '+ ' : '- ') . $row->lot . ' Lot';
            })
            ->editColumn('type', function($row) {
                if ($row->source_type === 'transfer') {
                    return '<span class="badge badge-light-primary">Transfer</span>';
                }
                if ($row->type === 'deposit' || $row->type === 'profit') {
                    return '<span class="badge badge-light-success">'.ucfirst($row->type).'</span>';
                }
                return '<span class="badge badge-light-danger">'.ucfirst($row->type).'</span>';
            })
            ->editColumn('amount', function($row) {
                if ($row->source_type === 'transfer') {
                    $color = $row->amount < 0 ? 'text-danger' : 'text-success';
                    $prefix = $row->amount < 0 ? '- ' : '+ ';
                    return '<span class="'.$color.' fw-bold">' . $prefix . 'Rp ' . number_format(abs($row->amount), 0, ',', '.') . '</span>';
                }
                $isIn = in_array($row->type, ['deposit', 'profit']);
                $color = $isIn ? 'text-success' : 'text-danger';
                $prefix = $isIn ? '+ ' : '- ';
                return '<span class="'.$color.' fw-bold">' . $prefix . 'Rp ' . number_format(abs($row->amount), 0, ',', '.') . '</span>';
            })
            ->addColumn('action', function($row) use ($ipoTrxIds, $tradeTrxIds) {
                if ($row->source_type === 'transfer') {
                    return '<span class="text-muted fs-7">Manage in Transfers</span>';
                }
                if ($ipoTrxIds->contains($row->id)) {
                    return '<span class="text-muted fs-7">Manage in IPO Orders</span>';
                }
                if ($tradeTrxIds->contains($row->id)) {
                    return '<span class="text-muted fs-7">Manage in Buy/Sell</span>';
                }
                return '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-stock-trx-btn" title="Remove from Tracking"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
            })
            ->rawColumns(['asset', 'lot', 'type', 'amount', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // Real deposit/withdrawal/profit/loss entries are created from the Portfolio show page,
        // and asset-tagged fundings from the Transfers page.
        return response()->json(['error' => 'Use the Portfolio or Transfers page to add a transaction'], 400);
    }

    public function destroy($id)
    {
        $this->financeStockTrackingService->deleteLedgerEntry(auth()->id(), $id);

        return response()->json(['success' => 'Transaction removed from tracking']);
    }

    public function updatePrice(Request $request)
    {
        $validated = $request->validate([
            'asset' => 'required|string|max:100',
            'current_price' => 'required|numeric|min:0.01',
        ]);

        $this->financeStockTrackingService->updatePrice(auth()->id(), $validated['asset'], $validated['current_price']);

        return response()->json(['success' => 'Harga saat ini berhasil diperbarui']);
    }

    public function storeTrade(Request $request)
    {
        $validated = $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'type' => 'required|in:buy,sell',
            'trade_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        try {
            $this->financeStockTrackingService->recordTrade(auth()->id(), $validated);
        } catch (FinanceDomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        $message = $validated['type'] === 'buy' ? 'Pembelian emiten berhasil dicatat' : 'Penjualan emiten berhasil dicatat';

        return response()->json(['success' => $message]);
    }

    public function destroyTrade($id)
    {
        $this->financeStockTrackingService->deleteTrade(auth()->id(), $id);

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
