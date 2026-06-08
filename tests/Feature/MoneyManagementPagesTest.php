<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class MoneyManagementPagesTest extends TestCase
{
    public function test_dashboard_page_loads_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();
        $this->assertNotNull($user, 'User rifqizaki72@gmail.com must exist in the database for this test.');

        $response = $this->actingAs($user)
            ->get('/money-management/dashboard');

        $response->assertStatus(200);
    }

    public function test_summary_page_loads_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $response = $this->actingAs($user)
            ->get('/money-management/summary');

        $response->assertStatus(200);
    }

    public function test_budgets_page_loads_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $response = $this->actingAs($user)
            ->get('/money-management/budgets');

        $response->assertStatus(200);
    }
}
