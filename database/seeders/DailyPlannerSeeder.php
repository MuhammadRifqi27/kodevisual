<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;
use App\Models\AppRole;
use App\Models\AppPermission;
use App\Models\User;
use App\Models\UserAppRole;

class DailyPlannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create the App
        $app = App::updateOrCreate(
            ['code' => 'daily-planner'],
            ['name' => 'Daily Planner', 'description' => 'Maintain and schedule your daily activities manually with listing and calendar views.']
        );

        // 2. Create Permissions for this App
        $permissions = [
            'dashboard' => 'View Dashboard',
            'activity' => 'Manage Activities (CRUD)',
            'recurring-activity' => 'Manage Recurring Activities (CRUD)',
        ];

        $permissionModels = [];
        foreach ($permissions as $code => $name) {
            $permissionModels[$code] = AppPermission::updateOrCreate(
                ['app_id' => $app->id, 'code' => $code],
                ['name' => $name]
            );
        }

        // 3. Create Roles for this App
        $adminRole = AppRole::updateOrCreate(
            ['app_id' => $app->id, 'code' => 'admin'],
            ['name' => 'Planner Master']
        );

        $userRole = AppRole::updateOrCreate(
            ['app_id' => $app->id, 'code' => 'user'],
            ['name' => 'Planner User']
        );

        // 4. Assign Permissions to Roles
        $adminRole->permissions()->sync(array_column($permissionModels, 'id'));
        
        $userRole->permissions()->sync([
            $permissionModels['dashboard']->id,
            $permissionModels['activity']->id,
        ]);

        // 5. Assign first user to this app as Admin if exists
        $user = User::first();
        if ($user) {
            UserAppRole::updateOrCreate(
                ['user_id' => $user->id, 'app_id' => $app->id],
                ['app_role_id' => $adminRole->id]
            );
        }
    }
}
