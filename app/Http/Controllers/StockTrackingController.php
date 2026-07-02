<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FinancePortfolio;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StockTrackingController extends Controller
{
    public function index()
    {
        // Get all portfolios related to Stocks
        $stockPortfolios = FinancePortfolio::where('user_id', auth()->id())
            ->whereHas('investment', function($q) {
                $q->where('type', 'stock');
            })
            ->get();

        $totalStockValue = $stockPortfolios->sum('balance');
        $portfolioIds = $stockPortfolios->pluck('id');

        // Per-emiten balance breakdown from investment transactions (deposit+profit - withdrawal-loss)...
        $balances = FinanceInvestmentTransaction::where('user_id', auth()->id())
            ->whereIn('finance_investment_id', $portfolioIds)
            ->get()
            ->groupBy(function($item) {
                return $item->asset ?: 'Unspecified';
            })
            ->map(function($rows) {
                return $rows->sum(function($row) {
                    return in_array($row->type, ['deposit', 'profit']) ? $row->amount : -$row->amount;
                });
            });

        // ...merged with asset-tagged Internal Transfer legs (amount is already signed per-account)
        $transferBalances = FinanceTransaction::where('user_id', auth()->id())
            ->where('type', 'transfer')
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotNull('asset')
            ->get()
            ->groupBy('asset')
            ->map(function($rows) {
                return $rows->sum('amount');
            });

        foreach ($transferBalances as $asset => $amount) {
            $balances[$asset] = ($balances[$asset] ?? 0) + $amount;
        }

        // Per-emiten lot breakdown, same merge approach as the Rupiah balance above
        $lots = FinanceInvestmentTransaction::where('user_id', auth()->id())
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotNull('lot')
            ->get()
            ->groupBy(function($item) {
                return $item->asset ?: 'Unspecified';
            })
            ->map(function($rows) {
                return $rows->sum(function($row) {
                    return in_array($row->type, ['deposit', 'profit']) ? $row->lot : -$row->lot;
                });
            });

        $transferLots = FinanceTransaction::where('user_id', auth()->id())
            ->where('type', 'transfer')
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotNull('lot')
            ->get()
            ->groupBy('asset')
            ->map(function($rows) {
                return $rows->sum('lot');
            });

        foreach ($transferLots as $asset => $lot) {
            $lots[$asset] = ($lots[$asset] ?? 0) + $lot;
        }

        $emitenBalances = $balances->keys()->merge($lots->keys())->unique()
            ->map(function($asset) use ($balances, $lots) {
                return [
                    'asset' => $asset,
                    'balance' => $balances[$asset] ?? 0,
                    'lot' => $lots[$asset] ?? null,
                ];
            })->values();

        return view('pages.money-management.stock-tracking.index', compact('stockPortfolios', 'totalStockValue', 'emitenBalances'));
    }

    public function datatable()
    {
        $userId = auth()->id();

        // Get Stock portfolio IDs
        $stockPortfolioIds = FinancePortfolio::where('user_id', $userId)
            ->whereHas('investment', function($q) {
                $q->where('type', 'stock');
            })
            ->pluck('id');

        $invTrx = FinanceInvestmentTransaction::where('user_id', $userId)
            ->whereIn('finance_investment_id', $stockPortfolioIds)
            ->with('portfolio')
            ->get()
            ->map(function($item) {
                $item->source_type = 'investment';
                return $item;
            });

        $transferTrx = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'transfer')
            ->whereIn('finance_investment_id', $stockPortfolioIds)
            ->whereNotNull('asset')
            ->with('portfolio')
            ->get()
            ->map(function($item) {
                $item->source_type = 'transfer';
                return $item;
            });

        $data = $invTrx->concat($transferTrx)->sortByDesc('date');

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
            ->addColumn('action', function($row) {
                if ($row->source_type === 'transfer') {
                    return '<span class="text-muted fs-7">Manage in Transfers</span>';
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
        $transaction = FinanceInvestmentTransaction::where('user_id', auth()->id())->findOrFail($id);
        $transaction->delete();
        return response()->json(['success' => 'Transaction removed from tracking']);
    }
}
