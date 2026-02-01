<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserApprovalController extends Controller
{
    public function index()
    {
        return view('pages.admin.user_approval');
    }

    public function datatable()
    {
        $users = User::with('role')->where('is_approved', 0)->get();
        return Datatables::of($users)
            ->addIndexColumn()
            ->addColumn('role_name', function($row){
                return $row->role ? ucfirst($row->role->name) : '-';
            })
            ->addColumn('action', function($row){
                $btn = '<button data-id="'.$row->id.'" class="btn btn-success btn-sm approve-btn">Approve</button>';
                return $btn;
            })
            ->editColumn('created_at', function($row){
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function listing()
    {
        return view('pages.admin.user_list');
    }

    public function listingDatatable()
    {
        $users = User::with('role')->get();
        return Datatables::of($users)
            ->addIndexColumn()
            ->addColumn('role_name', function($row){
                return $row->role ? ucfirst($row->role->name) : '-';
            })
            ->addColumn('status', function($row){
                return $row->is_approved ? '<span class="badge badge-success">Approved</span>' : '<span class="badge badge-warning">Pending</span>';
            })
            ->addColumn('action', function($row){
                $btn = '<a href="'.route('administrator.user-apps.index', $row->id).'" class="btn btn-info btn-sm">Apps</a>';
                $btn .= ' <a href="javascript:void(0)" data-id="'.$row->id.'" class="btn btn-primary btn-sm edit-btn ms-1">Edit</a>';
                $btn .= ' <a href="javascript:void(0)" data-id="'.$row->id.'" class="btn btn-danger btn-sm delete-btn ms-1">Delete</a>';
                return $btn;
            })
            ->editColumn('created_at', function($row){
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function approve($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_approved = true;
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'User approved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to approve user.']);
        }
    }
    
    public function destroy($id)
    {
        User::find($id)->delete();
        return response()->json(['success' => 'User deleted successfully.']);
    }
}
