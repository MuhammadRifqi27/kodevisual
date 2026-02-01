<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;
use App\Models\AppRole;
use App\Models\AppPermission;
use App\Models\User;
use App\Models\UserAppRole;

class AppStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create the App
        $moneyApp = App::updateOrCreate(
            ['code' => 'money-management'],
            ['name' => 'Money Management', 'description' => 'Manage personal finance, budgets, and investments.']
        );

        // 2. Create Permissions for this App
        $permissions = [
            'dashboard' => 'View Dashboard',
            'transactions' => 'Manage Transactions',
            'budgets' => 'Manage Budgets',
            'summary' => 'View Financial Summary',
            'portfolio' => 'Manage Portfolio',
            'settings' => 'Manage Settings',
        ];

        $permissionModels = [];
        foreach ($permissions as $code => $name) {
            $permissionModels[$code] = AppPermission::updateOrCreate(
                ['app_id' => $moneyApp->id, 'code' => $code],
                ['name' => $name]
            );
        }

        // 3. Create Roles for this App
        $adminRole = AppRole::updateOrCreate(
            ['app_id' => $moneyApp->id, 'code' => 'admin'],
            ['name' => 'Administrator']
        );

        $userRole = AppRole::updateOrCreate(
            ['app_id' => $moneyApp->id, 'code' => 'user'],
            ['name' => 'Regular User']
        );

        // 4. Assign Permissions to Roles
        $adminRole->permissions()->sync(array_column($permissionModels, 'id'));
        
        $userRole->permissions()->sync([
            $permissionModels['dashboard']->id,
            $permissionModels['transactions']->id,
            $permissionModels['summary']->id,
        ]);

        // 5. Assign first user to this app as Admin if exists
        $user = User::first();
        if ($user) {
            UserAppRole::updateOrCreate(
                ['user_id' => $user->id, 'app_id' => $moneyApp->id],
                ['app_role_id' => $adminRole->id]
            );
        }
    }
}
