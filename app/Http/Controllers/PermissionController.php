<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index()
    {
        return view('pages.admin.permissions.index');
    }

    public function datatable()
    {
        $permissions = Permission::query();
        return Datatables::of($permissions)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '<a href="javascript:void(0)" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit-btn">Edit</a>';
                $btn .= ' <a href="javascript:void(0)" data-id="'.$row->id.'" class="btn btn-danger btn-sm delete-btn">Delete</a>';
                return $btn;
            })
             ->editColumn('created_at', function($row){
                return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'description' => 'nullable|string',
        ]);

        Permission::create($request->all());

        return response()->json(['success' => 'Permission created successfully.']);
    }

    public function edit($id)
    {
        $permission = Permission::find($id);
        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $permission = Permission::find($id);
        $permission->update($request->all());

        return response()->json(['success' => 'Permission updated successfully.']);
    }

    public function destroy($id)
    {
        Permission::find($id)->delete();
        return response()->json(['success' => 'Permission deleted successfully.']);
    }
}
