<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'administrator'], ['description' => 'System Administrator']);
        Role::firstOrCreate(['name' => 'new-joiner'], ['description' => 'New User awaiting approval or limited access']);
    }
}
