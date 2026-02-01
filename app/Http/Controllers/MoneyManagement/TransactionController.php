<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use App\Models\FinanceInvestment;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function index()
    {
        $investments = FinanceInvestment::all();
        return view('pages.money-management.transactions.index', compact('investments'));
    }

    public function datatable(Request $request)
    {
        $data = FinanceTransaction::where('user_id', auth()->id())
            ->whereIn('type', ['income', 'expense']) // Exclude transfers from main list
            ->with('category');

        if ($request->has('type') && $request->type != 'all') {
            $data->where('type', $request->type);
        }

        return Datatables::of($data)
            ->addIndexColumn()
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
                return $row->investment ? $row->investment->name : '<span class="text-muted">No Portfolio</span>';
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
        $type = $request->type;
        $categories = FinanceCategory::where('type', $type)->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_investments,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        FinanceTransaction::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'type' => $request->type,
            'finance_category_id' => $request->category_id,
            'finance_investment_id' => $request->investment_id,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return response()->json(['success' => 'Transaksi berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:finance_categories,id',
            'investment_id' => 'nullable|exists:finance_investments,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $transaction = FinanceTransaction::where('user_id', auth()->id())->findOrFail($id);
        $transaction->update([
            'date' => $request->date,
            'type' => $request->type,
            'finance_category_id' => $request->category_id,
            'finance_investment_id' => $request->investment_id,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return response()->json(['success' => 'Transaksi berhasil diperbarui']);
    }

    public function destroy($id)
    {
        FinanceTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();
        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
