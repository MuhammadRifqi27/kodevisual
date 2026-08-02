<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(
            FinanceSetting::where('user_id', auth()->id())->get()->pluck('value', 'key')
        );
    }

    /**
     * Accepts a flat key-value payload, e.g. {"payroll_start_day": 25}.
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        foreach ($request->all() as $key => $value) {
            FinanceSetting::updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['success' => 'Pengaturan berhasil disimpan']);
    }
}
