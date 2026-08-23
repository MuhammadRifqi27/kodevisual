<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinancePortfolio\FinancePortfolioService;
use App\Services\FinanceTransfer\FinanceTransferService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    public function __construct(
        private FinanceTransferService $financeTransferService,
        private FinancePortfolioService $financePortfolioService,
    ) {
    }

    public function index()
    {
        $accounts = $this->financePortfolioService->listForUser(auth()->id());
        $account_investment = $accounts[0]->investment->name;
        return view('pages.money-management.transfers.index', compact('accounts', 'account_investment'));
    }

    public function datatable()
    {
        $data = $this->financeTransferService->listQuery(auth()->id());

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('date', function($row) {
                return date('d M Y', strtotime($row->date));
            })
            ->addColumn('from', function($row) {
                return $row->portfolio->account_name . ' - ' . $row->portfolio->investment->name ?? '-';
            })
            ->addColumn('to', function($row) {
                return $row->destinationPortfolio->account_name . ' - ' . $row->destinationPortfolio->investment->name ?? '-';
            })
            ->editColumn('amount', function($row) {
                return 'Rp ' . number_format(abs($row->amount), 0, ',', '.');
            })
            ->addColumn('action', function($row){
                $btn = '<button data-id="'.$row->id.'"
                        data-date="'.$row->date.'"
                        data-finance_investment_id="'.$row->finance_investment_id.'"
                        data-to_finance_investment_id="'.$row->to_finance_investment_id.'"
                        data-amount="'.abs($row->amount).'"
                        data-asset="'.$row->asset.'"
                        data-lot="'.($row->lot !== null ? abs($row->lot) : '').'"
                        data-description="'.e($row->description).'"
                        class="btn btn-icon btn-active-light-primary w-30px h-30px me-2 edit-transfer-btn" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-transfer-btn"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $outbound = $this->financeTransferService->transfer(auth()->id(), $validated);

        return response()->json([
            'success' => 'Transfer berhasil dicatat',
            'transaction_id' => $outbound->id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $this->financeTransferService->updateTransfer(auth()->id(), $id, $validated);

        return response()->json(['success' => 'Transfer berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $this->financeTransferService->deleteTransfer(auth()->id(), $id);

        return response()->json(['success' => 'Transfer berhasil dihapus']);
    }

    public function showReceipt($id)
    {
        $transaction = $this->financeTransferService->findForReceipt(auth()->id(), $id);

        return view('pages.money-management.transfers.receipt', compact('transaction'));
    }
}
