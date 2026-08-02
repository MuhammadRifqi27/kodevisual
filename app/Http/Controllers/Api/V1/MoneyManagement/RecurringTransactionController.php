<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinancePortfolio;
use App\Models\FinanceRecurringTransaction;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        $this->process();

        $data = FinanceRecurringTransaction::where('user_id', auth()->id())
            ->with(['category', 'portfolio'])
            ->orderBy('next_date')
            ->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'finance_category_id' => 'required|exists:finance_categories,id',
            'finance_investment_id' => 'required|exists:finance_portfolios,id',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        FinancePortfolio::where('user_id', auth()->id())->findOrFail($request->finance_investment_id);

        $recurring = FinanceRecurringTransaction::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'type' => $request->type,
            'finance_category_id' => $request->finance_category_id,
            'finance_investment_id' => $request->finance_investment_id,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'start_date' => $request->start_date,
            'next_date' => $request->start_date,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json($recurring, 201);
    }

    public function destroy($id)
    {
        FinanceRecurringTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();

        return response()->json(['success' => 'Recurring transaction berhasil dihapus']);
    }

    /**
     * Generate real transactions for every due recurring template
     * (next_date <= today, is_active) and advance next_date.
     *
     * Note: amount is always stored positive with `type` carrying the sign,
     * matching the convention used by TransactionController::store and
     * FinancePortfolio::getBalanceAttribute everywhere else in this module.
     */
    public function process()
    {
        $userId = auth()->id();
        $today = Carbon::today();

        $pendings = FinanceRecurringTransaction::where('user_id', $userId)
            ->where('is_active', true)
            ->where('next_date', '<=', $today)
            ->get();

        foreach ($pendings as $recurring) {
            FinanceTransaction::create([
                'user_id' => $userId,
                'date' => $recurring->next_date,
                'type' => $recurring->type,
                'finance_category_id' => $recurring->finance_category_id,
                'finance_investment_id' => $recurring->finance_investment_id,
                'amount' => $recurring->amount,
                'description' => '[Auto] ' . $recurring->name . ($recurring->description ? ': ' . $recurring->description : ''),
            ]);

            $next = Carbon::parse($recurring->next_date);
            switch ($recurring->frequency) {
                case 'daily': $next->addDay(); break;
                case 'weekly': $next->addWeek(); break;
                case 'monthly': $next->addMonth(); break;
                case 'yearly': $next->addYear(); break;
            }

            $recurring->update(['next_date' => $next]);
        }

        return response()->json(['processed' => $pendings->count()]);
    }
}
