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
                // $btn .= ' <button data-id="'.$row->id.'" class="btn btn-danger btn-sm reject-btn">Reject</button>';
                return $btn;
            })
            ->editColumn('created_at', function($row){
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['action'])
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
}
