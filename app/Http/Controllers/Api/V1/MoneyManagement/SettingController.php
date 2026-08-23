<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceSetting\FinanceSettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private FinanceSettingService $financeSettingService)
    {
    }

    public function index()
    {
        return response()->json($this->financeSettingService->allForUser(auth()->id()));
    }

    /**
     * Accepts a flat key-value payload, e.g. {"payroll_start_day": 25}.
     */
    public function store(Request $request)
    {
        $this->financeSettingService->saveMany(auth()->id(), $request->all());

        return response()->json(['success' => 'Pengaturan berhasil disimpan']);
    }
}
