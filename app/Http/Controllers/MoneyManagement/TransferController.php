<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;
use App\Models\FinanceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    public function index()
    {
        $accounts = FinancePortfolio::with('investment')->where('user_id', auth()->id())->get();
        $account_investment = $accounts[0]->investment->name;
        return view('pages.money-management.transfers.index', compact('accounts', 'account_investment'));
    }

    public function datatable()
    {
        $data = FinanceTransaction::where('user_id', auth()->id())
            ->where('type', 'transfer')
            ->where('amount', '<', 0) // Only show the sender side to avoid duplicate lines
            ->with(['portfolio', 'destinationPortfolio'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

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
        $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $userId = auth()->id();
        $lot = $request->filled('lot') ? (int) $request->lot : null;

        // Find or Create an internal "Transfer" category to keep schema consistent
        $category = FinanceCategory::firstOrCreate(
            ['name' => 'Internal Transfer', 'type' => 'expense'],
            ['description' => 'System created category for internal transfers']
        );

        $fromAccount = FinancePortfolio::with('investment')->where('user_id', $userId)->findOrFail($request->from_account_id);
        $toAccount = FinancePortfolio::with('investment')->where('user_id', $userId)->findOrFail($request->to_account_id);

        $transactionId = DB::transaction(function() use ($request, $userId, $category, $fromAccount, $toAccount, $lot) {
            // 1. Transaction FROM (Outbound)
            $from = FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $request->date,
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $request->from_account_id,
                'to_finance_investment_id' => $request->to_account_id,
                'asset' => $request->asset,
                'lot' => $lot !== null ? -$lot : null,
                'amount' => -$request->amount, // Negative
                'description' => $request->description ?? 'Transfer to ' . $toAccount->account_name . ' - ' . $toAccount->investment->name,
            ]);

            // 2. Transaction TO (Inbound)
            FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $request->date,
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $request->to_account_id,
                'to_finance_investment_id' => $request->from_account_id,
                'asset' => $request->asset,
                'lot' => $lot,
                'amount' => $request->amount, // Positive
                'description' => $request->description ?? 'Transfer from ' . $fromAccount->account_name . ' - ' . $fromAccount->investment->name,
            ]);

            return $from->id;
        });

        return response()->json([
            'success' => 'Transfer berhasil dicatat',
            'transaction_id' => $transactionId
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_portfolios,id',
            'to_account_id' => 'required|exists:finance_portfolios,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'asset' => 'nullable|string|max:100',
            'lot' => 'nullable|integer|min:0',
        ]);

        $userId = auth()->id();
        $lot = $request->filled('lot') ? (int) $request->lot : null;

        // Validate accounts belong to this user (parity with store())
        FinancePortfolio::where('user_id', $userId)->findOrFail($request->from_account_id);
        FinancePortfolio::where('user_id', $userId)->findOrFail($request->to_account_id);

        // The datatable only ever shows the outbound (negative-amount) leg, so $id always refers to it
        $outbound = FinanceTransaction::where('user_id', $userId)->where('type', 'transfer')->findOrFail($id);

        DB::transaction(function() use ($request, $userId, $outbound, $lot) {
            // Locate the inbound counterpart BEFORE mutating $outbound (same predicate as destroy())
            $inbound = FinanceTransaction::where('user_id', $userId)
                ->where('date', $outbound->date)
                ->where('type', 'transfer')
                ->where('finance_investment_id', $outbound->to_finance_investment_id)
                ->where('to_finance_investment_id', $outbound->finance_investment_id)
                ->where('amount', -$outbound->amount)
                ->first();

            $outbound->update([
                'date' => $request->date,
                'finance_investment_id' => $request->from_account_id,
                'to_finance_investment_id' => $request->to_account_id,
                'asset' => $request->asset,
                'lot' => $lot !== null ? -$lot : null,
                'amount' => -$request->amount,
                'description' => $request->description,
            ]);

            if ($inbound) {
                $inbound->update([
                    'date' => $request->date,
                    'finance_investment_id' => $request->to_account_id,
                    'to_finance_investment_id' => $request->from_account_id,
                    'asset' => $request->asset,
                    'lot' => $lot,
                    'amount' => $request->amount,
                    'description' => $request->description,
                ]);
            }
        });

        return response()->json(['success' => 'Transfer berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $transaction = FinanceTransaction::where('user_id', auth()->id())->findOrFail($id);
        
        // When deleting a transfer, we must find its pair to keep balances correct
        DB::transaction(function() use ($transaction) {
            // Find the counterpart: same date, same reversed accounts, same absolute amount
            FinanceTransaction::where('user_id', $transaction->user_id)
                ->where('date', $transaction->date)
                ->where('type', 'transfer')
                ->where('finance_investment_id', $transaction->to_finance_investment_id)
                ->where('to_finance_investment_id', $transaction->finance_investment_id)
                ->where('amount', -$transaction->amount)
                ->delete();

            $transaction->delete();
        });

        return response()->json(['success' => 'Transfer berhasil dihapus']);
    }
    public function showReceipt($id)
    {
        $transaction = FinanceTransaction::where('user_id', auth()->id())
            ->with(['portfolio', 'destinationPortfolio'])
            ->findOrFail($id);

        return view('pages.money-management.transfers.receipt', compact('transaction'));
    }
}
