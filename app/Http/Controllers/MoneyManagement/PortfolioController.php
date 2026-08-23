<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Exceptions\FinanceDomainException;
use App\Http\Controllers\Controller;
use App\Services\FinanceInvestment\FinanceInvestmentService;
use App\Services\FinancePortfolio\FinancePortfolioService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PortfolioController extends Controller
{
    public function __construct(
        private FinancePortfolioService $financePortfolioService,
        private FinanceInvestmentService $financeInvestmentService,
    ) {
    }

    public function index()
    {
        $globalInvestments = $this->financeInvestmentService->query()->get();
        return view('pages.money-management.portfolio.index', compact('globalInvestments'));
    }

    public function datatable()
    {
        $portfolios = $this->financePortfolioService->listForUser(auth()->id());

        return DataTables::of($portfolios)
            ->addIndexColumn()
            ->addColumn('code_name', function($row) {
                $code = $row->investment && $row->investment->code ? '<span class="badge badge-light-primary me-2">'.$row->investment->code.'</span>' : '';
                return $code . '<span class="fw-bold text-gray-800">'.$row->account_name.'</span>';
            })
            ->editColumn('balance', function($row) {
                return '<span class="fw-bolder">Rp ' . number_format($row->balance, 0, ',', '.') . '</span>';
            })
            ->addColumn('action', function($row){
                $btn = '<a href="'.route('money-management.portfolio.show', $row->id).'" class="btn btn-icon btn-active-light-primary w-30px h-30px me-2" title="View Details"><i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></a>';
                $btn .= '<button data-id="'.$row->id.'"
                        data-finance_investment_id="'.$row->finance_investment_id.'"
                        data-account_name="'.$row->account_name.'"
                        data-account_number="'.$row->account_number.'"
                        data-description="'.$row->description.'"
                        data-account_investment="'.($row->account_investment ? 1 : 0).'"
                        class="btn btn-icon btn-active-light-primary w-30px h-30px me-2 edit-portfolio-btn" title="Edit"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-portfolio-btn" title="Delete"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->rawColumns(['code_name', 'balance', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $portfolio = $this->financePortfolioService->assertOwned(auth()->id(), $id)->load('investment');
        $balance = $portfolio->balance;

        return view('pages.money-management.portfolio.show', compact('portfolio', 'balance'));
    }

    public function transactionDatatable($id)
    {
        $data = $this->financePortfolioService->activityFeed(auth()->id(), $id)
            ->each(function ($item) {
                if ($item->source_type === 'general') {
                    $item->asset = null;
                    $item->lot = null;
                    $item->display_type = $item->type;
                }
            });

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('date', function($row) {
                return date('d M Y', strtotime($row->date));
            })
            ->editColumn('type', function($row) {
                if ($row->source_type == 'general') {
                    $color = 'primary';
                    if($row->type == 'income') $color = 'success';
                    if($row->type == 'expense') $color = 'danger';

                    $cat = $row->category ? ' ('.$row->category->name.')' : '';
                    return '<span class="badge badge-light-'.$color.'">'.ucfirst($row->type).$cat.'</span>';
                }

                if($row->type == 'deposit' || $row->type == 'profit') {
                    return '<span class="badge badge-light-success">'.ucfirst($row->type).'</span>';
                }
                return '<span class="badge badge-light-danger">'.ucfirst($row->type).'</span>';
            })
            ->editColumn('amount', function($row) {
                $amount = $row->source_type == 'general' ? $row->display_amount : $row->amount;
                $color = $amount < 0 ? 'text-danger' : 'text-success';
                return '<span class="'.$color.' fw-bold">' . ($amount < 0 ? '-' : '+') . ' Rp ' . number_format(abs($amount), 0, ',', '.') . '</span>';
            })
            ->editColumn('asset', function($row) {
                return $row->asset ? '<span class="badge badge-light-info">'.$row->asset.'</span>' : '<span class="text-muted">-</span>';
            })
            ->editColumn('lot', function($row) {
                return $row->lot !== null ? $row->lot.' Lot' : '<span class="text-muted">-</span>';
            })
            ->addColumn('action', function($row){
                if ($row->source_type == 'general') {
                    return '<span class="text-muted fs-7">Manage in Transactions</span>';
                }
                 $btn = '<button data-id="'.$row->id.'"
                        data-date="'.$row->date.'"
                        data-type="'.$row->type.'"
                        data-amount="'.$row->amount.'"
                        data-asset="'.$row->asset.'"
                        data-lot="'.$row->lot.'"
                        data-description="'.$row->description.'"
                        class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 edit-trx-btn">
                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-trx-btn">
                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>';
                return $btn;
            })
            ->rawColumns(['type', 'amount', 'asset', 'lot', 'action'])
            ->make(true);
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

        $this->financePortfolioService->createPortfolio(auth()->id(), $validated);

        return response()->json(['success' => 'Akun Portofolio berhasil ditambahkan']);
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

        $this->financePortfolioService->updatePortfolio(auth()->id(), $id, $validated);

        return response()->json(['success' => 'Akun Portofolio berhasil diperbarui']);
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

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
            'date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal,profit,loss',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $portfolioId = $validated['finance_investment_id'];
        unset($validated['finance_investment_id']);

        $this->financePortfolioService->createLedgerEntry(auth()->id(), $portfolioId, $validated);

        return response()->json(['success' => 'Transaksi berhasil disimpan']);
    }

    public function updateTransaction(Request $request, $id)
    {
        $validated = $request->validate([
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
            'date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal,profit,loss',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $this->financePortfolioService->updateLedgerEntry(auth()->id(), $id, $validated);

        return response()->json(['success' => 'Transaksi berhasil diperbarui']);
    }

    public function destroyTransaction($id)
    {
        $this->financePortfolioService->deleteLedgerEntry(auth()->id(), $id);
        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
