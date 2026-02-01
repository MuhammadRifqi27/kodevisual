<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceInvestment;
use App\Models\FinanceTransaction;
use App\Models\FinanceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    public function index()
    {
        $accounts = FinanceInvestment::orderBy('name')->get();
        return view('pages.money-management.transfers.index', compact('accounts'));
    }

    public function datatable()
    {
        $data = FinanceTransaction::where('user_id', auth()->id())
            ->where('type', 'transfer')
            ->with(['investment', 'destinationAccount'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('date', function($row) {
                return date('d M Y', strtotime($row->date));
            })
            ->addColumn('from', function($row) {
                return $row->investment->name ?? '-';
            })
            ->addColumn('to', function($row) {
                return $row->destinationAccount->name ?? '-';
            })
            ->editColumn('amount', function($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->addColumn('action', function($row){
                $btn = '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-transfer-btn"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'from_account_id' => 'required|exists:finance_investments,id',
            'to_account_id' => 'required|exists:finance_investments,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $userId = auth()->id();
        
        // Find or Create an internal "Transfer" category to keep schema consistent
        $category = FinanceCategory::firstOrCreate(
            ['name' => 'Internal Transfer', 'type' => 'expense'],
            ['description' => 'System created category for internal transfers']
        );

        DB::transaction(function() use ($request, $userId, $category) {
            // 1. Transaction FROM (Outbound)
            FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $request->date,
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $request->from_account_id,
                'to_finance_investment_id' => $request->to_account_id,
                'amount' => -$request->amount, // Negative
                'description' => $request->description ?? 'Transfer to ' . FinanceInvestment::find($request->to_account_id)->name,
            ]);

            // 2. Transaction TO (Inbound)
            FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $request->date,
                'type' => 'transfer',
                'finance_category_id' => $category->id,
                'finance_investment_id' => $request->to_account_id,
                'to_finance_investment_id' => $request->from_account_id,
                'amount' => $request->amount, // Positive
                'description' => $request->description ?? 'Transfer from ' . FinanceInvestment::find($request->from_account_id)->name,
            ]);
        });

        return response()->json(['success' => 'Transfer berhasil dicatat']);
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
}
