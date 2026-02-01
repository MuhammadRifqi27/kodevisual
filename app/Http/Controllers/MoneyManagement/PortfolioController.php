<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinancePortfolio;
use App\Models\FinanceInvestment;
use App\Models\FinanceInvestmentTransaction;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PortfolioController extends Controller
{
    public function index()
    {
        $globalInvestments = FinanceInvestment::orderBy('name')->get();
        return view('pages.money-management.portfolio.index', compact('globalInvestments'));
    }

    public function datatable()
    {
        $portfolios = FinancePortfolio::where('user_id', auth()->id())
            ->with('investment')
            ->get();

        return Datatables::of($portfolios)
            ->addIndexColumn()
            ->addColumn('code_name', function($row) {
                $code = $row->investment && $row->investment->code ? '<span class="badge badge-light-primary me-2">'.$row->investment->code.'</span>' : '';
                return $code . '<span class="fw-bold text-gray-800">'.$row->account_name.'</span>';
            })
            ->editColumn('balance', function($row) {
                return '<span class="fw-bolder text-dark">Rp ' . number_format($row->balance, 0, ',', '.') . '</span>';
            })
            ->addColumn('action', function($row){
                $btn = '<a href="'.route('money-management.portfolio.show', $row->id).'" class="btn btn-icon btn-active-light-primary w-30px h-30px me-2" title="View Details"><i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></a>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-portfolio-btn" title="Delete"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->rawColumns(['code_name', 'balance', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $portfolio = FinancePortfolio::where('user_id', auth()->id())
            ->with(['investment'])
            ->findOrFail($id);
        
        $balance = $portfolio->balance;

        return view('pages.money-management.portfolio.show', compact('portfolio', 'balance'));
    }

    public function transactionDatatable($id)
    {
        // Specific Investment Transactions
        $invTrx = FinanceInvestmentTransaction::where('finance_investment_id', $id)
            ->where('user_id', auth()->id())
            ->get()
            ->map(function($item) {
                $item->source_type = 'investment';
                return $item;
            });

        // General Transactions linked to this portfolio
        $genTrx = FinanceTransaction::where('finance_investment_id', $id)
            ->where('user_id', auth()->id())
            ->with('category')
            ->get()
            ->map(function($item) {
                $item->source_type = 'general';
                $item->display_type = $item->type;
                if ($item->type === 'transfer') {
                    $item->display_amount = $item->amount; // Use signed amount for transfer
                } else {
                    $item->display_amount = $item->type === 'expense' ? -$item->amount : $item->amount;
                }
                return $item;
            });

        $data = $invTrx->concat($genTrx)->sortByDesc('date');

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
            ->addColumn('action', function($row){
                if ($row->source_type == 'general') {
                    return '<span class="text-muted fs-7">Manage in Transactions</span>';
                }
                 $btn = '<button data-id="'.$row->id.'" 
                        data-date="'.$row->date.'" 
                        data-type="'.$row->type.'" 
                        data-amount="'.$row->amount.'" 
                        data-description="'.$row->description.'" 
                        class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 edit-trx-btn">
                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-trx-btn">
                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>';
                return $btn;
            })
            ->rawColumns(['type', 'amount', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_investments,id',
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        FinancePortfolio::create($data);

        return response()->json(['success' => 'Akun Portofolio berhasil ditambahkan']);
    }

    public function destroy($id)
    {
        $portfolio = FinancePortfolio::where('user_id', auth()->id())->findOrFail($id);
        
        // Check if has transactions
        if ($portfolio->generalTransactions()->exists() || $portfolio->transactions()->exists()) {
            return response()->json(['error' => 'Tidak bisa menghapus akun yang sudah memiliki riwayat transaksi'], 400);
        }

        $portfolio->delete();
        return response()->json(['success' => 'Akun Portofolio berhasil dihapus']);
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'date' => 'required|date',
            'type' => 'required',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $portfolio = FinancePortfolio::where('user_id', auth()->id())->findOrFail($request->finance_investment_id);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        FinanceInvestmentTransaction::create($data);

        return response()->json(['success' => 'Transaksi berhasil disimpan']);
    }

    public function updateTransaction(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $trx = FinanceInvestmentTransaction::where('user_id', auth()->id())->findOrFail($id);
        $trx->update($request->all());

        return response()->json(['success' => 'Transaksi berhasil diperbarui']);
    }

    public function destroyTransaction($id)
    {
        FinanceInvestmentTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();
        return response()->json(['success' => 'Transaksi berhasil dihapus']);
    }
}
