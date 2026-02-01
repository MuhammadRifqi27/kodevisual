<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceRecurringTransaction;
use App\Models\FinanceCategory;
use App\Models\FinancePortfolio;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        $this->processPending();
        $categories = FinanceCategory::orderBy('name')->get();
        $accounts = FinancePortfolio::where('user_id', auth()->id())->get();
        return view('pages.money-management.transactions.recurring', compact('categories', 'accounts'));
    }

    public function datatable()
    {
        $data = FinanceRecurringTransaction::where('user_id', auth()->id())
            ->with(['category', 'portfolio'])
            ->orderBy('next_date', 'asc');

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
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'finance_category_id' => 'required|exists:finance_categories,id',
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
        ]);

        FinancePortfolio::where('user_id', auth()->id())->findOrFail($request->finance_investment_id);

        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['next_date'] = $request->start_date; // Initially next_date is start_date

        FinanceRecurringTransaction::create($data);

        return response()->json(['success' => 'Recurring transaction berhasil dibuat']);
    }

    public function destroy($id)
    {
        $recurring = FinanceRecurringTransaction::where('user_id', auth()->id())->findOrFail($id);
        $recurring->delete();

        return response()->json(['success' => 'Recurring transaction berhasil dihapus']);
    }

    public function processPending()
    {
        $userId = auth()->id();
        $today = Carbon::today();
        
        $pendings = FinanceRecurringTransaction::where('user_id', $userId)
            ->where('is_active', true)
            ->where('next_date', '<=', $today)
            ->get();

        foreach ($pendings as $recurring) {
            // Create the real transaction
            FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $recurring->next_date,
                'type' => $recurring->type,
                'finance_category_id' => $recurring->finance_category_id,
                'finance_investment_id' => $recurring->finance_investment_id,
                'amount' => $recurring->type == 'expense' ? -$recurring->amount : $recurring->amount,
                'description' => '[Auto] ' . $recurring->name . ($recurring->description ? ': ' . $recurring->description : ''),
            ]);

            // Update next_date based on frequency
            $next = Carbon::parse($recurring->next_date);
            switch ($recurring->frequency) {
                case 'daily': $next->addDay(); break;
                case 'weekly': $next->addWeek(); break;
                case 'monthly': $next->addMonth(); break;
                case 'yearly': $next->addYear(); break;
            }
            
            $recurring->update(['next_date' => $next]);
        }
    }
}
