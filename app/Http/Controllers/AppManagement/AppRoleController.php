<?php

namespace App\Http\Controllers\AppManagement;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\AppRole;
use App\Models\AppPermission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AppRoleController extends Controller
{
    public function index(Request $request)
    {
        $apps = App::all();
        $permissions = AppPermission::all()->groupBy('app_id');
        $selectedApp = $request->app_id ? App::find($request->app_id) : null;
        return view('pages.admin.app-roles.index', compact('apps', 'permissions', 'selectedApp'));
    }

    public function datatable(Request $request)
    {
        $roles = AppRole::with('app');
        
        if ($request->app_id) {
            $roles->where('app_id', $request->app_id);
        }

        return DataTables::of($roles)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $actions = [
                    [
                        'label' => 'Edit',
                        'icon' => 'pencil',
                        'color' => 'primary',
                        'attr' => ['data-id' => $row->id],
                        'onclick' => 'void(0)',
                        'class' => 'edit-btn'
                    ],
                    [
                        'label' => 'Delete',
                        'icon' => 'trash',
                        'color' => 'danger',
                        'attr' => ['data-id' => $row->id],
                        'onclick' => 'void(0)',
                        'class' => 'delete-btn'
                    ],
                ];

                return view('components.action-button', [
                    'id' => $row->id,
                    'actions' => $actions
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'app_id' => 'required|exists:apps,id',
            'name' => 'required',
            'code' => 'required',
            'permissions' => 'array'
        ]);

        $role = AppRole::create($request->only('app_id', 'name', 'code'));
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json(['success' => 'Role created successfully.']);
    }

    public function edit($id)
    {
        $role = AppRole::with('permissions')->find($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'app_id' => 'required|exists:apps,id',
            'name' => 'required',
            'code' => 'required',
            'permissions' => 'array'
        ]);

        $role = AppRole::find($id);
        $role->update($request->only('app_id', 'name', 'code'));
        $role->permissions()->sync($request->permissions ?? []);

        return response()->json(['success' => 'Role updated successfully.']);
    }

    public function destroy($id)
    {
        AppRole::find($id)->delete();
        return response()->json(['success' => 'Role deleted successfully.']);
    }
}
