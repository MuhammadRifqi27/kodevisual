<?php

namespace App\Services\FinanceCycle;

use App\Repositories\FinanceSetting\FinanceSettingRepository;
use Carbon\Carbon;

class FinanceCycleServiceImplement implements FinanceCycleService
{
    public function __construct(private FinanceSettingRepository $financeSettingRepository)
    {
    }

    public function forUser(int $userId, int $month, int $year): array
    {
        $payrollDay = $this->financeSettingRepository->get($userId, 'payroll_start_day', 25);

        return $this->forPayrollDay($payrollDay, $month, $year);
    }

    public function forPayrollDay($payrollDay, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $prevMonthDate = (clone $startDate)->subMonth();
        $nextMonthDate = (clone $startDate)->addMonth();

        $calculateStartForBase = function ($base) use ($payrollDay) {
            if ($payrollDay === 'last') {
                return (clone $base)->endOfMonth()->startOfDay();
            }

            $dayToUse = min((int) $payrollDay, $base->daysInMonth);
            return (clone $base)->day($dayToUse)->startOfDay();
        };

        if ($payrollDay === 'last' || (int) $payrollDay > 15) {
            $cycleStartDate = $calculateStartForBase($prevMonthDate);
            $nextCycleStartDate = $calculateStartForBase($startDate);
        } else {
            $cycleStartDate = $calculateStartForBase($startDate);
            $nextCycleStartDate = $calculateStartForBase($nextMonthDate);
        }

        $cycleEndDate = (clone $nextCycleStartDate)->subSecond();

        return [
            'start' => $cycleStartDate,
            'end' => $cycleEndDate,
            'next_start' => $nextCycleStartDate,
        ];
    }
}
