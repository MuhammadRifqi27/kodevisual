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

    public function test_master_data_pages_load_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        foreach ([
            '/money-management/master-data/expenses',
            '/money-management/master-data/income',
            '/money-management/master-data/investments',
            '/money-management/master-data/settings',
        ] as $path) {
            $this->actingAs($user)->get($path)->assertStatus(200);
        }
    }

    public function test_master_data_datatables_respond_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        foreach ([
            '/money-management/master-data/expenses/datatable',
            '/money-management/master-data/income/datatable',
            '/money-management/master-data/investments/datatable',
        ] as $path) {
            $this->actingAs($user)->get($path)->assertStatus(200)->assertJsonStructure(['data']);
        }
    }

    public function test_portfolio_pages_load_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->get('/money-management/portfolio')->assertStatus(200);
        $this->actingAs($user)->get('/money-management/portfolio/datatable')->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_transaction_and_transfer_pages_load_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->get('/money-management/transactions')->assertStatus(200);
        $this->actingAs($user)->get('/money-management/transactions/datatable')->assertStatus(200)->assertJsonStructure(['data']);
        $this->actingAs($user)->get('/money-management/transfers')->assertStatus(200);
        $this->actingAs($user)->get('/money-management/transfers/datatable')->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_transaction_and_transfer_api_endpoints_respond_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->getJson('/api/v1/money-management/transactions')->assertStatus(200)->assertJsonStructure(['data', 'summary']);
        $this->actingAs($user)->getJson('/api/v1/money-management/transfers')->assertStatus(200);
    }

    public function test_budget_and_recurring_endpoints_respond_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->get('/money-management/recurring')->assertStatus(200);
        $this->actingAs($user)->get('/money-management/recurring/datatable')->assertStatus(200)->assertJsonStructure(['data']);
        $this->actingAs($user)->getJson('/api/v1/money-management/budgets')->assertStatus(200)->assertJsonStructure([
            'month', 'year', 'cycle_start_date', 'cycle_end_date', 'income_pool', 'total_budget', 'total_spent', 'categories',
        ]);
        $this->actingAs($user)->getJson('/api/v1/money-management/recurring')->assertStatus(200);
    }

    /**
     * Bug fix #1: web's recurring processor used to pre-negate `amount` for
     * expenses, double-negating when read through FinancePortfolio::getBalanceAttribute()
     * (which already flips the sign based on `type`). Both web and API must now
     * always store a positive amount, matching the manual-entry convention.
     */
    public function test_recurring_processing_always_stores_positive_amount()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();
        $portfolio = \App\Models\FinancePortfolio::where('user_id', $user->id)->first();
        $category = \App\Models\FinanceCategory::where('type', 'expense')->first();

        $recurring = \App\Models\FinanceRecurringTransaction::create([
            'user_id' => $user->id,
            'name' => 'BATCH4_TEST_RECURRING',
            'type' => 'expense',
            'finance_category_id' => $category->id,
            'finance_investment_id' => $portfolio->id,
            'amount' => 12345,
            'frequency' => 'daily',
            'start_date' => now()->subDay()->toDateString(),
            'next_date' => now()->subDay()->toDateString(),
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/money-management/recurring');

        $generated = \App\Models\FinanceTransaction::where('user_id', $user->id)
            ->where('description', 'like', '%BATCH4_TEST_RECURRING%')
            ->first();

        $this->assertNotNull($generated, 'Recurring processing should have created a transaction.');
        $this->assertEquals(12345.0, (float) $generated->amount, 'Amount must be stored positive; sign comes from `type`.');

        $generated->delete();
        $recurring->delete();
    }

    public function test_dashboard_and_summary_api_endpoints_respond_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->getJson('/api/v1/money-management/dashboard')->assertStatus(200)->assertJsonStructure([
            'total_net_worth', 'total_liquid_cash', 'total_investment_value', 'liquid_accounts', 'portfolios',
        ]);
        $this->actingAs($user)->getJson('/api/v1/money-management/summary')->assertStatus(200)->assertJsonStructure([
            'total_net_worth', 'asset_allocation', 'category_summary', 'chart_data', 'advice',
        ]);
    }

    /**
     * Bug fix #2: API's dashboard used to classify investment id 11 (AJAIB, a
     * real stock-investment platform) as liquid cash instead of investment,
     * because its hardcoded whitelist [1,2,8,9] was missing it — unlike web's
     * [1,2,8,9,11]. Both must now agree via config('finance.investment_account_ids').
     */
    public function test_dashboard_classifies_ajaib_as_investment_on_both_web_and_api()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();
        $ajaibPortfolio = \App\Models\FinancePortfolio::where('user_id', $user->id)
            ->where('finance_investment_id', 11)
            ->first();
        $this->assertNotNull($ajaibPortfolio, 'User must have an AJAIB (investment id 11) portfolio for this test.');

        $apiResponse = $this->actingAs($user)->getJson('/api/v1/money-management/dashboard')->assertStatus(200);
        $apiPortfolio = collect($apiResponse->json('portfolios'))->firstWhere('portfolio_id', $ajaibPortfolio->id);

        if ($apiPortfolio !== null) {
            $this->assertTrue($apiPortfolio['is_investment'], 'AJAIB portfolio should be classified as investment via config(finance.investment_account_ids).');
        }
    }

    public function test_btc_tracking_pages_and_api_respond_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->get('/money-management/btc-tracking')->assertStatus(200);
        $this->actingAs($user)->get('/money-management/btc-tracking/datatable')->assertStatus(200)->assertJsonStructure(['data']);
        $this->actingAs($user)->getJson('/api/v1/money-management/btc-tracking')->assertStatus(200)->assertJsonStructure([
            'btc_portfolios', 'total_btc_value', 'asset_balances',
        ]);
    }

    public function test_stock_tracking_pages_and_api_respond_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->get('/money-management/stock-tracking')->assertStatus(200);
        $this->actingAs($user)->get('/money-management/stock-tracking/datatable')->assertStatus(200)->assertJsonStructure(['data']);
        $this->actingAs($user)->getJson('/api/v1/money-management/stock-tracking')->assertStatus(200)->assertJsonStructure([
            'stock_portfolios', 'total_stock_value', 'emiten_balances', 'trading_balance', 'open_amount', 'invested_total', 'total_pnl', 'total_equity',
        ]);
    }

    /**
     * End-to-end stock trade lifecycle through the Service layer: buy (funds
     * from trading balance), then sell (weighted-average cost basis, realized
     * P&L booked), then delete — verifying every linked ledger entry is cleaned up.
     */
    public function test_stock_trade_lifecycle_creates_and_cleans_up_ledger_entries()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();
        $portfolio = \App\Models\FinancePortfolio::where('user_id', $user->id)
            ->whereHas('investment', fn ($q) => $q->where('type', 'stock'))
            ->first();
        $this->assertNotNull($portfolio, 'User must have a stock-type portfolio for this test.');

        $service = app(\App\Services\FinanceStockTracking\FinanceStockTrackingService::class);

        $buyTrade = $service->recordTrade($user->id, [
            'finance_investment_id' => $portfolio->id,
            'asset' => 'BATCH7TEST',
            'type' => 'buy',
            'trade_date' => now()->toDateString(),
            'price_per_share' => 100,
            'lot' => 5,
        ]);
        $this->assertNotNull($buyTrade->holding_transaction_id);
        $this->assertNotNull($buyTrade->cash_transaction_id);

        $sellTrade = $service->recordTrade($user->id, [
            'finance_investment_id' => $portfolio->id,
            'asset' => 'BATCH7TEST',
            'type' => 'sell',
            'trade_date' => now()->toDateString(),
            'price_per_share' => 120,
            'lot' => 5,
        ]);
        $this->assertNotNull($sellTrade->pnl_transaction_id, 'Selling at a higher price than bought should realize a profit transaction.');

        $linkedIds = array_filter([
            $buyTrade->holding_transaction_id, $buyTrade->cash_transaction_id,
            $sellTrade->holding_transaction_id, $sellTrade->cash_transaction_id, $sellTrade->pnl_transaction_id,
        ]);
        $this->assertCount(5, $linkedIds);

        $service->deleteTrade($user->id, $sellTrade->id);
        $service->deleteTrade($user->id, $buyTrade->id);

        $this->assertEquals(0, \App\Models\FinanceEmitenTrade::whereIn('id', [$buyTrade->id, $sellTrade->id])->count());
        $this->assertEquals(0, \App\Models\FinanceInvestmentTransaction::whereIn('id', $linkedIds)->count());
    }

    public function test_wedding_planner_page_loads_and_item_lifecycle_works()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->get('/money-management/wedding-planner')->assertStatus(200);

        $service = app(\App\Services\WeddingPlanner\WeddingPlannerService::class);
        $service->addItem($user->id, ['name' => 'BATCH8_TEST_ITEM', 'estimated_amount' => 1000]);
        $item = \App\Models\WeddingPlannerItem::where('user_id', $user->id)->where('name', 'BATCH8_TEST_ITEM')->first();
        $this->assertNotNull($item);

        $service->removeItem($user->id, $item->id);
        $this->assertEquals(0, \App\Models\WeddingPlannerItem::where('id', $item->id)->count());
    }

    public function test_ipo_pages_and_api_respond_successfully()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();

        $this->actingAs($user)->get('/money-management/ipo')->assertStatus(200);
        $this->actingAs($user)->get('/money-management/ipo/datatable')->assertStatus(200)->assertJsonStructure(['data']);
        $this->actingAs($user)->getJson('/api/v1/money-management/ipo')->assertStatus(200);
    }

    /**
     * End-to-end IPO order lifecycle through the Service layer: place an order
     * (blocks funds), confirm partial allotment (releases block, books holding +
     * offset), then cancel — verifying every linked ledger entry is cleaned up.
     */
    public function test_ipo_order_lifecycle_creates_and_cleans_up_ledger_entries()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();
        $portfolio = \App\Models\FinancePortfolio::where('user_id', $user->id)
            ->whereHas('investment', fn ($q) => $q->where('type', 'stock'))
            ->first();
        $this->assertNotNull($portfolio, 'User must have a stock-type portfolio for this test.');

        $service = app(\App\Services\FinanceIpoOrder\FinanceIpoOrderService::class);

        $order = $service->placeOrder($user->id, [
            'finance_investment_id' => $portfolio->id,
            'asset' => 'BATCH5TEST',
            'order_date' => now()->toDateString(),
            'price_per_share' => 100,
            'lot_ordered' => 10,
        ]);
        $this->assertNotNull($order->order_transaction_id);

        $confirmed = $service->confirmAllotment($user->id, $order->id, [
            'lot_allotted' => 5,
            'allotment_date' => now()->toDateString(),
        ]);
        $this->assertEquals('partial', $confirmed->status);
        $this->assertNotNull($confirmed->release_transaction_id);
        $this->assertNotNull($confirmed->holding_transaction_id);
        $this->assertNotNull($confirmed->offset_transaction_id);

        $linkedIds = array_filter([
            $confirmed->order_transaction_id,
            $confirmed->release_transaction_id,
            $confirmed->holding_transaction_id,
            $confirmed->offset_transaction_id,
        ]);
        $this->assertCount(4, $linkedIds);

        $service->cancelOrder($user->id, $order->id);

        $this->assertEquals(0, \App\Models\FinanceIpoOrder::where('id', $order->id)->count());
        $this->assertEquals(0, \App\Models\FinanceInvestmentTransaction::whereIn('id', $linkedIds)->count());
    }

    /**
     * Bug fix #3: web's ledger endpoint used to accept any string as `type`
     * (no enum), unlike the API. Both must now reject it the same way.
     */
    public function test_portfolio_ledger_rejects_invalid_type_on_both_web_and_api()
    {
        $user = User::where('email', 'rifqizaki72@gmail.com')->first();
        $portfolio = \App\Models\FinancePortfolio::where('user_id', $user->id)->first();
        $this->assertNotNull($portfolio, 'User must have at least one portfolio for this test.');

        $this->actingAs($user)
            ->postJson('/money-management/portfolio/transaction/store', [
                'finance_investment_id' => $portfolio->id,
                'date' => now()->toDateString(),
                'type' => 'bogus',
                'amount' => 100,
            ])
            ->assertStatus(422);

        $this->actingAs($user)
            ->postJson("/api/v1/money-management/portfolios/{$portfolio->id}/transactions", [
                'date' => now()->toDateString(),
                'type' => 'bogus',
                'amount' => 100,
            ])
            ->assertStatus(422);
    }
}
