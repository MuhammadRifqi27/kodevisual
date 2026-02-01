<?php

namespace App\Http\Controllers\AppManagement;

use App\Http\Controllers\Controller;
use App\Models\App;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AppController extends Controller
{
    public function index()
    {
        return view('pages.admin.apps.index');
    }

    public function datatable()
    {
        $apps = App::query();
        return DataTables::of($apps)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $actions = [
                    [
                        'label' => 'Roles',
                        'icon' => 'setting-2',
                        'color' => 'info',
                        'url' => route('administrator.app-roles.index', ['app_id' => $row->id]),
                    ],
                    [
                        'label' => 'Permissions',
                        'icon' => 'key',
                        'color' => 'warning',
                        'url' => route('administrator.app-permissions.index', ['app_id' => $row->id]),
                    ],
                    [
                        'label' => 'Edit',
                        'icon' => 'pencil',
                        'color' => 'primary',
                        'attr' => ['data-id' => $row->id],
                        'onclick' => 'void(0)', // Placeholder for JS click handler
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
            'name' => 'required',
            'code' => 'required|unique:apps,code',
            'description' => 'nullable',
        ]);

        App::create($request->all());
        return response()->json(['success' => 'Application created successfully.']);
    }

    public function edit($id)
    {
        return response()->json(App::find($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:apps,code,' . $id,
            'description' => 'nullable',
        ]);

        $app = App::find($id);
        $app->update($request->all());
        return response()->json(['success' => 'Application updated successfully.']);
    }

    public function destroy($id)
    {
        App::find($id)->delete();
        return response()->json(['success' => 'Application deleted successfully.']);
    }
}
