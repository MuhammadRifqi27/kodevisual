<?php

namespace App\Http\Controllers;

use App\Services\FinanceBtcTracking\FinanceBtcTrackingService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BtcTrackingController extends Controller
{
    public function __construct(private FinanceBtcTrackingService $financeBtcTrackingService)
    {
    }

    public function index()
    {
        $overview = $this->financeBtcTrackingService->overview(auth()->id());
        $btcPortfolios = $overview['portfolios'];
        $totalBtcValue = $overview['totalValue'];
        $assetBalances = $overview['assetBalances'];

        return view('pages.money-management.btc-tracking.index', compact('btcPortfolios', 'totalBtcValue', 'assetBalances'));
    }

    public function datatable()
    {
        $data = $this->financeBtcTrackingService->activityFeed(auth()->id());

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
            ->addColumn('action', function($row) {
                if ($row->source_type === 'transfer') {
                    return '<span class="text-muted fs-7">Manage in Transfers</span>';
                }
                return '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-btc-trx-btn" title="Remove from Tracking"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
            })
            ->rawColumns(['asset', 'type', 'amount', 'action'])
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
        $this->financeBtcTrackingService->deleteLedgerEntry(auth()->id(), $id);

        return response()->json(['success' => 'Transaction removed from tracking']);
    }
}
