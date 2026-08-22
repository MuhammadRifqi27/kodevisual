<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return view('pages.admin.roles.index', compact('permissions'));
    }

    public function datatable()
    {
        $roles = Role::with('permissions');
        return Datatables::of($roles)
            ->addIndexColumn()
            ->addColumn('permissions', function($row){
                $permissions = $row->permissions->pluck('name')->toArray();
                return implode(', ', $permissions);
            })
            ->addColumn('action', function($row){
                $btn = '<a href="javascript:void(0)" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit-btn">Edit/Permissions</a>';
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
            'name' => 'required|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($request->only('name', 'description'));

        $role->permissions()->sync($request->input('permissions', []));

        return response()->json(['success' => 'Role created successfully.']);
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->find($id);
        $allPermissions = Permission::all();
        return response()->json([
            'role' => $role,
            'rolePermissions' => $role->permissions->pluck('id')->toArray(),
            'allPermissions' => $allPermissions
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::find($id);
        $role->update($request->only('name', 'description'));

        $role->permissions()->sync($request->input('permissions', []));

        return response()->json(['success' => 'Role updated successfully.']);
    }

    public function destroy($id)
    {
        Role::find($id)->delete();
        return response()->json(['success' => 'Role deleted successfully.']);
    }
}
