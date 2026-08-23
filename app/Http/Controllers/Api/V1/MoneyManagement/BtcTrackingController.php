<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceBtcTracking\FinanceBtcTrackingService;

class BtcTrackingController extends Controller
{
    public function __construct(private FinanceBtcTrackingService $financeBtcTrackingService)
    {
    }

    public function index()
    {
        $overview = $this->financeBtcTrackingService->overview(auth()->id());

        return response()->json([
            'btc_portfolios' => $overview['portfolios'],
            'total_btc_value' => $overview['totalValue'],
            'asset_balances' => $overview['assetBalances'],
        ]);
    }

    public function destroy($id)
    {
        $this->financeBtcTrackingService->deleteLedgerEntry(auth()->id(), $id);

        return response()->json(['success' => 'Transaction removed from tracking']);
    }
}
