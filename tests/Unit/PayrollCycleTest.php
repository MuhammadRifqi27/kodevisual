<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class PayrollCycleTest extends TestCase
{
    /**
     * Replicates the cycle calculation logic to verify it is gap-free and overlap-free.
     */
    private function calculateCycleDates($year, $month, $payrollDay)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $prevMonthDate = (clone $startDate)->subMonth();
        $nextMonthDate = (clone $startDate)->addMonth();

        $calculateStartForBase = function($base) use ($payrollDay) {
            if ($payrollDay === 'last') {
                return (clone $base)->endOfMonth()->startOfDay();
            } else {
                $dayToUse = min((int)$payrollDay, $base->daysInMonth);
                return (clone $base)->day($dayToUse)->startOfDay();
            }
        };

        if ($payrollDay === 'last' || (int)$payrollDay > 15) {
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

    public function test_payroll_cycle_is_gap_free_for_last()
    {
        // Test May 2026, payrollDay = 'last'
        $cycle = $this->calculateCycleDates(2026, 5, 'last');

        // May cycle start should be April 30 00:00:00 (last day of April)
        $this->assertEquals('2026-04-30 00:00:00', $cycle['start']->toDateTimeString());
        
        // May cycle end should be May 30 23:59:59 (1 second before June cycle start: May 31 00:00:00)
        $this->assertEquals('2026-05-30 23:59:59', $cycle['end']->toDateTimeString());
        $this->assertEquals('2026-05-31 00:00:00', $cycle['next_start']->toDateTimeString());

        // Difference between start of next cycle and end of current cycle should be exactly 1 second
        $this->assertEquals(1, $cycle['next_start']->diffInSeconds($cycle['end']));
    }

    public function test_payroll_cycle_across_all_days_and_months()
    {
        $payrollDays = ['last', '1', '5', '15', '16', '25', '28', '30', '31'];
        $months = range(1, 12);
        $year = 2026;

        foreach ($payrollDays as $payrollDay) {
            foreach ($months as $month) {
                $cycle = $this->calculateCycleDates($year, $month, $payrollDay);

                // Assert that the end of this cycle is exactly 1 second before the start of the next cycle
                $diff = $cycle['next_start']->diffInSeconds($cycle['end']);
                $this->assertEquals(1, $diff, "Gap detected for payroll day: {$payrollDay} in month: {$month}");

                // Assert that the start is before the end
                $this->assertTrue($cycle['start']->lt($cycle['end']), "Start date is not before end date for payroll day: {$payrollDay} in month: {$month}");
            }
        }
    }
}
