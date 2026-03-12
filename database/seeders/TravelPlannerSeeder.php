<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;
use App\Models\AppRole;
use App\Models\AppPermission;
use App\Models\User;
use App\Models\UserAppRole;

class TravelPlannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create the App
        $travelApp = App::updateOrCreate(
            ['code' => 'travel-planner'],
            ['name' => 'Travel Planner', 'description' => 'Plan your trips, manage itineraries, and track travel budgets.']
        );

        // 2. Create Permissions for this App
        $permissions = [
            'dashboard' => 'View Dashboard',
            'trips' => 'Manage Trips & Itineraries',
            'budgets' => 'Manage Travel Budgets',
        ];

        $permissionModels = [];
        foreach ($permissions as $code => $name) {
            $permissionModels[$code] = AppPermission::updateOrCreate(
                ['app_id' => $travelApp->id, 'code' => $code],
                ['name' => $name]
            );
        }

        // 3. Create Roles for this App
        $adminRole = AppRole::updateOrCreate(
            ['app_id' => $travelApp->id, 'code' => 'admin'],
            ['name' => 'Trip Master']
        );

        $userRole = AppRole::updateOrCreate(
            ['app_id' => $travelApp->id, 'code' => 'user'],
            ['name' => 'Traveler']
        );

        // 4. Assign Permissions to Roles
        $adminRole->permissions()->sync(array_column($permissionModels, 'id'));
        
        $userRole->permissions()->sync([
            $permissionModels['dashboard']->id,
            $permissionModels['trips']->id,
        ]);

        // 5. Assign first user to this app as Admin if exists
        $user = User::first();
        if ($user) {
            UserAppRole::updateOrCreate(
                ['user_id' => $user->id, 'app_id' => $travelApp->id],
                ['app_role_id' => $adminRole->id]
            );
        }
    }
}
