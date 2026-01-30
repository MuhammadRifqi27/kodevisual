<?php

namespace App\Http\Controllers\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use App\Models\FinanceInvestment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterDataController extends Controller
{
    // =========================================================================
    // EXPENSES
    // =========================================================================
    public function expensesIndex()
    {
        return view('pages.money-management.master-data.expenses');
    }

    public function expensesDatatable()
    {
        $data = FinanceCategory::where('type', 'expense');
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '<button data-id="'.$row->id.'" data-name="'.$row->name.'" data-type="'.$row->type.'" data-description="'.$row->description.'" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 edit-category-btn"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-category-btn"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->editColumn('type', function($row) {
                return '<span class="badge badge-light-danger">Expense</span>';
            })
            ->rawColumns(['action', 'type'])
            ->make(true);
    }

    // =========================================================================
    // INCOME
    // =========================================================================
    public function incomeIndex()
    {
        return view('pages.money-management.master-data.income');
    }

    public function incomeDatatable()
    {
        $data = FinanceCategory::where('type', 'income');
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '<button data-id="'.$row->id.'" data-name="'.$row->name.'" data-type="'.$row->type.'" data-description="'.$row->description.'" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 edit-category-btn"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-category-btn"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->editColumn('type', function($row) {
                return '<span class="badge badge-light-success">Income</span>';
            })
            ->rawColumns(['action', 'type'])
            ->make(true);
    }

    // =========================================================================
    // INVESTMENTS
    // =========================================================================
    public function investmentsIndex()
    {
        return view('pages.money-management.master-data.investments');
    }

    public function investmentsDatatable()
    {
        $data = FinanceInvestment::query();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('name', function($row) {
                return '<span class="fw-bold text-gray-800">'.$row->name.'</span>';
            })
            ->addColumn('code', function($row) {
                return $row->code ? '<span class="badge badge-light-primary">'.$row->code.'</span>' : '-';
            })
            ->addColumn('action', function($row){
                $btn = '<button data-id="'.$row->id.'" data-name="'.$row->name.'" data-code="'.$row->code.'" data-description="'.$row->description.'" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 edit-investment-btn"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
                $btn .= '<button data-id="'.$row->id.'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-investment-btn"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                return $btn;
            })
            ->rawColumns(['action', 'name', 'code'])
            ->make(true);
    }

    // =========================================================================
    // CRUD ACTIONS
    // =========================================================================
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        FinanceCategory::create($request->all());

        return response()->json(['success' => 'Kategori berhasil disimpan']);
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $category = FinanceCategory::findOrFail($id);
        $category->update($request->all());

        return response()->json(['success' => 'Kategori berhasil diperbarui']);
    }

    public function destroyCategory($id)
    {
        FinanceCategory::findOrFail($id)->delete();
        return response()->json(['success' => 'Kategori berhasil dihapus']);
    }

    public function storeInvestment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        FinanceInvestment::create($request->all());

        return response()->json(['success' => 'Data investasi berhasil disimpan']);
    }

    public function updateInvestment(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $investment = FinanceInvestment::findOrFail($id);
        $investment->update($request->all());

        return response()->json(['success' => 'Data investasi berhasil diperbarui']);
    }

    public function destroyInvestment($id)
    {
        FinanceInvestment::findOrFail($id)->delete();
        return response()->json(['success' => 'Data investasi berhasil dihapus']);
    }
}
