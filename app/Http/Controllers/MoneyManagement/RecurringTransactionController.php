<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceCategory\FinanceCategoryService;
use App\Services\FinancePortfolio\FinancePortfolioService;
use App\Services\FinanceRecurringTransaction\FinanceRecurringTransactionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RecurringTransactionController extends Controller
{
    public function __construct(
        private FinanceRecurringTransactionService $financeRecurringTransactionService,
        private FinanceCategoryService $financeCategoryService,
        private FinancePortfolioService $financePortfolioService,
    ) {
    }

    public function index()
    {
        $this->financeRecurringTransactionService->processDue(auth()->id());
        $categories = $this->financeCategoryService->query()->get();
        $accounts = $this->financePortfolioService->listForUser(auth()->id());
        return view('pages.money-management.transactions.recurring', compact('categories', 'accounts'));
    }

    public function datatable()
    {
        $data = $this->financeRecurringTransactionService->listQuery(auth()->id());

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('type', function($row) {
                return ucfirst($row->type);
            })
            ->editColumn('amount', function($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('is_active', function($row) {
                $status = $row->is_active ? 'Active' : 'Paused';
                $color = $row->is_active ? 'success' : 'danger';
                return "<span class=\"badge badge-light-$color\">$status</span>";
            })
            ->addColumn('action', function($row) {
                $btn = '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-primary w-30px h-30px edit-recurring-btn me-2"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-recurring-btn"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'finance_category_id' => 'required|exists:finance_categories,id',
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $this->financeRecurringTransactionService->createRecurring(auth()->id(), $validated);

        return response()->json(['success' => 'Recurring transaction berhasil dibuat']);
    }

    public function destroy($id)
    {
        $this->financeRecurringTransactionService->deleteRecurring(auth()->id(), $id);

        return response()->json(['success' => 'Recurring transaction berhasil dihapus']);
    }
}
