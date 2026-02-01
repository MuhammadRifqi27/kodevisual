<?php

namespace App\Http\Controllers\AppManagement;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\AppPermission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AppPermissionController extends Controller
{
    public function index(Request $request)
    {
        $apps = App::all();
        $selectedApp = $request->app_id ? App::find($request->app_id) : null;
        return view('pages.admin.app-permissions.index', compact('apps', 'selectedApp'));
    }

    public function datatable(Request $request)
    {
        $permissions = AppPermission::with('app');
        
        if ($request->app_id) {
            $permissions->where('app_id', $request->app_id);
        }

        return DataTables::of($permissions)
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
        ]);

        AppPermission::create($request->all());
        return response()->json(['success' => 'Permission created successfully.']);
    }

    public function edit($id)
    {
        return response()->json(AppPermission::find($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'app_id' => 'required|exists:apps,id',
            'name' => 'required',
            'code' => 'required',
        ]);

        $permission = AppPermission::find($id);
        $permission->update($request->all());
        return response()->json(['success' => 'Permission updated successfully.']);
    }

    public function destroy($id)
    {
        AppPermission::find($id)->delete();
        return response()->json(['success' => 'Permission deleted successfully.']);
    }
}
