<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;
use App\Models\FinanceInvestment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BtcTrackingController extends Controller
{
    public function index()
    {
        // Get all portfolios related to Bitcoin
        $btcPortfolios = FinancePortfolio::where('user_id', auth()->id())
            ->where(function($query) {
                $query->whereHas('investment', function($q) {
                    $q->where('name', 'like', '%Bitcoin%')
                      ->orWhere('code', 'like', '%BTC%');
                })->orWhere('account_name', 'like', '%Bitcoin%')
                  ->orWhere('account_name', 'like', '%BTC%');
            })
            ->get();

        $totalBtcValue = $btcPortfolios->sum('balance');

        return view('pages.money-management.btc-tracking.index', compact('btcPortfolios', 'totalBtcValue'));
    }

    public function datatable()
    {
        $userId = auth()->id();
        
        // Get Bitcoin portfolio IDs
        $btcPortfolioIds = FinancePortfolio::where('user_id', $userId)
            ->where(function($query) {
                $query->whereHas('investment', function($q) {
                    $q->where('name', 'like', '%Bitcoin%')
                      ->orWhere('code', 'like', '%BTC%');
                })->orWhere('account_name', 'like', '%Bitcoin%')
                  ->orWhere('account_name', 'like', '%BTC%');
            })
            ->pluck('id');

        $data = FinanceTransaction::where('user_id', $userId)
            ->whereIn('finance_investment_id', $btcPortfolioIds)
            ->whereNot(function($query) use ($btcPortfolioIds) {
                // For internal transfers between two BTC portfolios, we only show the 'In' side (positive amount)
                // to avoid showing the same movement twice.
                $query->where('type', 'transfer')
                      ->whereIn('to_finance_investment_id', $btcPortfolioIds)
                      ->where('amount', '<', 0);
            })
            ->with(['portfolio.investment', 'destinationPortfolio.investment', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('date', function($row) {
                return date('d M Y', strtotime($row->date));
            })
            ->addColumn('description_display', function($row) use ($btcPortfolioIds) {
                $isSourceBtc = $btcPortfolioIds->contains($row->finance_investment_id);
                $isDestBtc = $btcPortfolioIds->contains($row->to_finance_investment_id);

                $text = $row->description;
                
                if ($row->type === 'transfer') {
                    $from = $row->portfolio->account_name ?? 'Unknown';
                    $to = $row->destinationPortfolio->account_name ?? 'Unknown';
                    
                    if ($isSourceBtc && $isDestBtc) {
                        return '<span class="badge badge-light-primary">Internal BTC Move</span><br><small class="text-muted">'.$from.' ➔ '.$to.'</small>';
                    } elseif ($isSourceBtc) {
                        // Since we only show the BTC side, if the amount is positive, it's a top-up from the destination
                        if ($row->amount > 0) {
                            return '<span class="badge badge-light-success">BTC Top-up</span><br><small class="text-muted">From '.$to.'</small>';
                        } else {
                            return '<span class="badge badge-light-danger">BTC Withdrawal</span><br><small class="text-muted">To '.$to.'</small>';
                        }
                    }
                }
                
                return $text ?: ucfirst($row->type);
            })
            ->editColumn('amount', function($row) use ($btcPortfolioIds) {
                $isSourceBtc = $btcPortfolioIds->contains($row->finance_investment_id);
                $isDestBtc = $btcPortfolioIds->contains($row->to_finance_investment_id);

                $amount = $row->amount;
                $color = 'text-dark';
                $prefix = '';
                
                if ($row->type === 'transfer') {
                    if ($isSourceBtc && $isDestBtc) {
                        // Internal BTC transfer doesn't change total BTC value
                        $color = 'text-primary';
                        $prefix = '↻ ';
                    } else {
                        // Top-up or Withdrawal
                        if ($amount > 0) {
                            $color = 'text-success';
                            $prefix = '+ ';
                        } else {
                            $color = 'text-danger';
                            $prefix = '- ';
                        }
                    }
                } else {
                    if ($row->type === 'expense') {
                        $color = 'text-danger';
                        $prefix = '- ';
                    } else {
                        $color = 'text-success';
                        $prefix = '+ ';
                    }
                }

                return '<span class="'.$color.' fw-bold">' . $prefix . 'Rp ' . number_format(abs($amount), 0, ',', '.') . '</span>';
            })
            ->addColumn('portfolio_name', function($row) use ($btcPortfolioIds) {
                if ($row->type === 'transfer') {
                    $isSourceBtc = $btcPortfolioIds->contains($row->finance_investment_id);
                    if ($isSourceBtc) return $row->portfolio->account_name;
                    return $row->destinationPortfolio->account_name;
                }
                return $row->portfolio->account_name ?? '-';
            })
            ->rawColumns(['description_display', 'amount'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // For now, let's say we use general transaction store or transfer store
        // But if they want a specific one, we can implement it here.
        return response()->json(['error' => 'Use Transactions or Transfers to add data'], 400);
    }

    public function destroy($id)
    {
        $transaction = FinanceTransaction::where('user_id', auth()->id())->findOrFail($id);
        $transaction->delete();
        return response()->json(['success' => 'Transaction removed from tracking']);
    }
}
