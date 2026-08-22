<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FinancePortfolio;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinanceTransaction;
use App\Models\FinanceIpoOrder;
use App\Models\FinanceEmitenPrice;
use App\Models\FinanceEmitenTrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // IPO orders still awaiting allotment: their order-block transaction is excluded
        // from the per-emiten breakdown below (it's not a real holding yet) and instead
        // surfaced as its own "Open" bucket.
        $pendingOrders = FinanceIpoOrder::where('user_id', auth()->id())
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNull('lot_allotted')
            ->get();
        $pendingOrderTrxIds = $pendingOrders->pluck('order_transaction_id')->filter()->values();
        $openAmount = $pendingOrders->sum('order_amount');

        // Per-emiten balance breakdown from investment transactions (deposit+profit - withdrawal-loss)...
        $balances = FinanceInvestmentTransaction::where('user_id', auth()->id())
            ->whereIn('finance_investment_id', $portfolioIds)
            ->whereNotIn('id', $pendingOrderTrxIds)
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
            ->whereNotIn('id', $pendingOrderTrxIds)
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

        $prices = FinanceEmitenPrice::where('user_id', auth()->id())->pluck('current_price', 'asset');

        $emitenBalances = $balances->keys()->merge($lots->keys())->unique()
            ->reject(function($asset) {
                return $asset === 'Unspecified';
            })
            ->map(function($asset) use ($balances, $lots, $prices) {
                $balance = $balances[$asset] ?? 0;
                $lot = $lots[$asset] ?? null;
                $price = $prices[$asset] ?? null;
                $shares = $lot !== null ? $lot * 100 : null;
                $marketValue = ($price !== null && $shares !== null) ? $shares * $price : $balance;
                return [
                    'asset' => $asset,
                    'balance' => $balance,
                    'lot' => $lot,
                    'current_price' => $price,
                    'market_value' => $marketValue,
                    'pnl' => $marketValue - $balance,
                ];
            })->values();

        // Trading balance = total value minus whatever is specifically allocated to a
        // named emiten — i.e. cash still free/uncommitted and available for new orders.
        // $balances is an Eloquent Collection (inherited from the ->get() call it was
        // built from) whose except() expects Model items, so sum it manually instead.
        $namedEmitenTotal = $balances->sum() - ($balances['Unspecified'] ?? 0);
        $tradingBalance = $totalStockValue - $namedEmitenTotal;
        $investedTotal = $namedEmitenTotal;
        $totalPnl = $emitenBalances->sum('pnl');
        $totalEquity = $tradingBalance + $openAmount + $investedTotal + $totalPnl;

        return view('pages.money-management.stock-tracking.index', compact(
            'stockPortfolios', 'totalStockValue', 'emitenBalances', 'tradingBalance',
            'openAmount', 'investedTotal', 'totalPnl', 'totalEquity'
        ));
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

        $data = $invTrx->concat($transferTrx)->sortByDesc('id');

        // Transactions generated by IPO orders or Buy/Sell trades must be managed as a
        // pair/group there, not deleted individually here (that would silently corrupt
        // the linked order/trade's balances).
        $ipoTrxIds = FinanceIpoOrder::where('user_id', $userId)
            ->whereIn('finance_investment_id', $stockPortfolioIds)
            ->get(['order_transaction_id', 'release_transaction_id', 'holding_transaction_id', 'offset_transaction_id'])
            ->flatMap(function($order) {
                return [$order->order_transaction_id, $order->release_transaction_id, $order->holding_transaction_id, $order->offset_transaction_id];
            })->filter()->values();

        $tradeTrxIds = FinanceEmitenTrade::where('user_id', $userId)
            ->whereIn('finance_investment_id', $stockPortfolioIds)
            ->get(['holding_transaction_id', 'cash_transaction_id', 'pnl_transaction_id'])
            ->flatMap(function($trade) {
                return [$trade->holding_transaction_id, $trade->cash_transaction_id, $trade->pnl_transaction_id];
            })->filter()->values();

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
        $transaction = FinanceInvestmentTransaction::where('user_id', auth()->id())->findOrFail($id);
        $transaction->delete();
        return response()->json(['success' => 'Transaction removed from tracking']);
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'asset' => 'required|string|max:100',
            'current_price' => 'required|numeric|min:0.01',
        ]);

        FinanceEmitenPrice::updateOrCreate(
            ['user_id' => auth()->id(), 'asset' => $request->asset],
            ['current_price' => $request->current_price]
        );

        return response()->json(['success' => 'Harga saat ini berhasil diperbarui']);
    }

    public function storeTrade(Request $request)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'required|string|max:100',
            'type' => 'required|in:buy,sell',
            'trade_date' => 'required|date',
            'price_per_share' => 'required|numeric|min:0.01',
            'lot' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $userId = auth()->id();
        $portfolio = FinancePortfolio::where('user_id', $userId)
            ->whereHas('investment', fn($q) => $q->where('type', 'stock'))
            ->findOrFail($request->finance_investment_id);

        $amount = $request->lot * 100 * $request->price_per_share;

        if ($request->type === 'buy') {
            $tradingBalance = $this->portfolioTradingBalance($portfolio->id);
            if ($amount > $tradingBalance) {
                return response()->json(['error' => 'Trading balance tidak cukup. Tersedia Rp ' . number_format($tradingBalance, 0, ',', '.')], 400);
            }

            DB::transaction(function() use ($request, $userId, $portfolio, $amount) {
                $holdingTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'asset' => $request->asset,
                    'lot' => $request->lot,
                    'date' => $request->trade_date,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'description' => 'Buy ' . $request->asset . ': ' . $request->lot . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.'),
                ]);

                $cashTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'date' => $request->trade_date,
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'description' => 'Buy ' . $request->asset . ': funded from trading balance',
                ]);

                FinanceEmitenTrade::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'asset' => $request->asset,
                    'type' => 'buy',
                    'trade_date' => $request->trade_date,
                    'price_per_share' => $request->price_per_share,
                    'lot' => $request->lot,
                    'description' => $request->description,
                    'holding_transaction_id' => $holdingTrx->id,
                    'cash_transaction_id' => $cashTrx->id,
                ]);
            });

            return response()->json(['success' => 'Pembelian emiten berhasil dicatat']);
        }

        // Sell
        [$currentLot, $currentInvested] = $this->portfolioEmitenState($portfolio->id, $request->asset);

        if ($request->lot > $currentLot) {
            return response()->json(['error' => 'Lot tidak cukup. Anda hanya punya ' . $currentLot . ' lot ' . $request->asset], 400);
        }

        $costOfGoodsSold = $currentLot > 0 ? ($currentInvested * $request->lot / $currentLot) : 0;
        $saleProceeds = $amount;
        $realizedPnl = $saleProceeds - $costOfGoodsSold;

        DB::transaction(function() use ($request, $userId, $portfolio, $costOfGoodsSold, $realizedPnl) {
            $holdingTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolio->id,
                'asset' => $request->asset,
                'lot' => $request->lot,
                'date' => $request->trade_date,
                'type' => 'withdrawal',
                'amount' => $costOfGoodsSold,
                'description' => 'Sell ' . $request->asset . ': ' . $request->lot . ' lot @ Rp ' . number_format($request->price_per_share, 0, ',', '.'),
            ]);

            $cashTrx = FinanceInvestmentTransaction::create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolio->id,
                'date' => $request->trade_date,
                'type' => 'deposit',
                'amount' => $costOfGoodsSold,
                'description' => 'Sell ' . $request->asset . ': capital returned to trading balance',
            ]);

            $pnlTrxId = null;
            if (abs($realizedPnl) > 0.004) {
                $pnlTrx = FinanceInvestmentTransaction::create([
                    'user_id' => $userId,
                    'finance_investment_id' => $portfolio->id,
                    'date' => $request->trade_date,
                    'type' => $realizedPnl > 0 ? 'profit' : 'loss',
                    'amount' => abs($realizedPnl),
                    'description' => 'Sell ' . $request->asset . ': realized ' . ($realizedPnl > 0 ? 'profit' : 'loss'),
                ]);
                $pnlTrxId = $pnlTrx->id;
            }

            FinanceEmitenTrade::create([
                'user_id' => $userId,
                'finance_investment_id' => $portfolio->id,
                'asset' => $request->asset,
                'type' => 'sell',
                'trade_date' => $request->trade_date,
                'price_per_share' => $request->price_per_share,
                'lot' => $request->lot,
                'description' => $request->description,
                'holding_transaction_id' => $holdingTrx->id,
                'cash_transaction_id' => $cashTrx->id,
                'pnl_transaction_id' => $pnlTrxId,
            ]);
        });

        return response()->json(['success' => 'Penjualan emiten berhasil dicatat']);
    }

    public function destroyTrade($id)
    {
        $trade = FinanceEmitenTrade::where('user_id', auth()->id())->findOrFail($id);

        DB::transaction(function() use ($trade) {
            FinanceInvestmentTransaction::whereIn('id', array_filter([
                $trade->holding_transaction_id,
                $trade->cash_transaction_id,
                $trade->pnl_transaction_id,
            ]))->delete();

            $trade->delete();
        });

        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }

    private function portfolioPendingOrderTrxIds($portfolioId)
    {
        return FinanceIpoOrder::where('user_id', auth()->id())
            ->where('finance_investment_id', $portfolioId)
            ->whereNull('lot_allotted')
            ->pluck('order_transaction_id')
            ->filter()
            ->values();
    }

    private function portfolioNamedEmitenBalances($portfolioId)
    {
        $pendingOrderTrxIds = $this->portfolioPendingOrderTrxIds($portfolioId);

        $balances = FinanceInvestmentTransaction::where('user_id', auth()->id())
            ->where('finance_investment_id', $portfolioId)
            ->whereNotIn('id', $pendingOrderTrxIds)
            ->get()
            ->groupBy(function($item) {
                return $item->asset ?: 'Unspecified';
            })
            ->map(function($rows) {
                return $rows->sum(function($row) {
                    return in_array($row->type, ['deposit', 'profit']) ? $row->amount : -$row->amount;
                });
            });

        $transferBalances = FinanceTransaction::where('user_id', auth()->id())
            ->where('type', 'transfer')
            ->where('finance_investment_id', $portfolioId)
            ->whereNotNull('asset')
            ->get()
            ->groupBy('asset')
            ->map(function($rows) {
                return $rows->sum('amount');
            });

        foreach ($transferBalances as $asset => $amount) {
            $balances[$asset] = ($balances[$asset] ?? 0) + $amount;
        }

        return $balances;
    }

    private function portfolioTradingBalance($portfolioId)
    {
        $portfolio = FinancePortfolio::findOrFail($portfolioId);
        $balances = $this->portfolioNamedEmitenBalances($portfolioId);
        $namedEmitenTotal = $balances->sum() - ($balances['Unspecified'] ?? 0);
        return $portfolio->balance - $namedEmitenTotal;
    }

    private function portfolioEmitenState($portfolioId, $asset)
    {
        $balances = $this->portfolioNamedEmitenBalances($portfolioId);
        $invested = $balances[$asset] ?? 0;

        $pendingOrderTrxIds = $this->portfolioPendingOrderTrxIds($portfolioId);

        $lots = FinanceInvestmentTransaction::where('user_id', auth()->id())
            ->where('finance_investment_id', $portfolioId)
            ->whereNotIn('id', $pendingOrderTrxIds)
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
            ->where('finance_investment_id', $portfolioId)
            ->whereNotNull('lot')
            ->get()
            ->groupBy('asset')
            ->map(function($rows) {
                return $rows->sum('lot');
            });

        foreach ($transferLots as $a => $lot) {
            $lots[$a] = ($lots[$a] ?? 0) + $lot;
        }

        $lot = $lots[$asset] ?? 0;

        return [$lot, $invested];
    }
}
