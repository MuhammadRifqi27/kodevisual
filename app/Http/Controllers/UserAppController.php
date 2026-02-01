<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\App;
use App\Models\AppRole;
use App\Models\UserAppRole;
use Illuminate\Http\Request;

class UserAppController extends Controller
{
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $apps = App::all();
        $userApps = UserAppRole::where('user_id', $userId)->with('app', 'role')->get();
        return view('pages.admin.users.apps', compact('user', 'apps', 'userApps'));
    }

    public function getAppRoles($appId)
    {
        $roles = AppRole::where('app_id', $appId)->get();
        return response()->json($roles);
    }

    public function assignApp(Request $request, $userId)
    {
        $request->validate([
            'app_id' => 'required|exists:apps,id',
            'app_role_id' => 'required|exists:app_roles,id',
        ]);

        UserAppRole::updateOrCreate(
            ['user_id' => $userId, 'app_id' => $request->app_id],
            ['app_role_id' => $request->app_role_id]
        );

        return response()->json(['success' => 'Application access assigned successfully.']);
    }

    public function removeApp($userId, $appId)
    {
        UserAppRole::where('user_id', $userId)->where('app_id', $appId)->delete();
        return response()->json(['success' => 'Application access removed successfully.']);
    }
}
