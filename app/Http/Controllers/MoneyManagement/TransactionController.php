<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceCategory\FinanceCategoryService;
use App\Services\FinancePortfolio\FinancePortfolioService;
use App\Services\FinanceTransaction\FinanceTransactionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function __construct(
        private FinanceTransactionService $financeTransactionService,
        private FinanceCategoryService $financeCategoryService,
        private FinancePortfolioService $financePortfolioService,
    ) {
    }

    public function index()
    {
        $investments = $this->financePortfolioService->listForUser(auth()->id());
        $categories = $this->financeCategoryService->query()->get();
        return view('pages.money-management.transactions.index', compact('investments', 'categories'));
    }

    public function datatable(Request $request)
    {
        $data = $this->financeTransactionService->filteredQuery(auth()->id(), $request->only([
            'type', 'category_id', 'start_date', 'end_date',
        ]));

        $summary = $this->financeTransactionService->summaryFor($data);

        return Datatables::of($data)
            ->addIndexColumn()
            ->with([
                'total_income' => 'Rp ' . number_format($summary['total_income'], 0, ',', '.'),
                'total_expense' => 'Rp ' . number_format($summary['total_expense'], 0, ',', '.'),
                'net_balance' => 'Rp ' . number_format($summary['net_balance'], 0, ',', '.'),
                'net_balance_raw' => $summary['net_balance'],
            ])
            ->editColumn('date', function($row) {
                return date('d M Y', strtotime($row->date));
            })
            ->editColumn('amount', function($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->addColumn('category_name', function($row) {
                return $row->category ? $row->category->name : '-';
            })
            ->addColumn('investment_name', function($row) {
                return $row->portfolio ? $row->portfolio->account_name : '<span class="text-muted">No Portfolio</span>';
            })
            ->editColumn('type', function($row) {
                if($row->type == 'income') return '<span class="badge badge-light-success">Income</span>';
                if($row->type == 'expense') return '<span class="badge badge-light-danger">Expense</span>';
                return '<span class="badge badge-light-primary">Transfer</span>';
            })
            ->addColumn('action', function($row){
                $btn = '<button data-id="'.$row->id.'"
                        data-date="'.$row->date.'"
                        data-type="'.$row->type.'"
                        data-category="'.$row->finance_category_id.'"
                        data-investment="'.$row->finance_investment_id.'"
                        data-amount="'.$row->amount.'"
                        data-description="'.$row->description.'"
                        class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 edit-transaction-btn">
                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-transaction-btn">
                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>';
                return $btn;
            })
            ->rawColumns(['type', 'action', 'investment_name'])
            ->make(true);
    }

    public function getCategories(Request $request)
    {
        $categories = $this->financeCategoryService->query($request->type)->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $this->financeTransactionService->createTransaction(auth()->id(), $validated);

        return response()->json(['success' => 'Transaksi berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $this->financeTransactionService->updateTransaction(auth()->id(), $id, $validated);

        return response()->json(['success' => 'Transaksi berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $this->financeTransactionService->deleteTransaction(auth()->id(), $id);
        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
