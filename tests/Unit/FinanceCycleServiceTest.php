<?php

namespace Tests\Unit;

use App\Services\FinanceCycle\FinanceCycleServiceImplement;
use App\Repositories\FinanceSetting\FinanceSettingRepository;
use PHPUnit\Framework\TestCase;

class FinanceCycleServiceTest extends TestCase
{
    private function service(): FinanceCycleServiceImplement
    {
        return new FinanceCycleServiceImplement($this->createStub(FinanceSettingRepository::class));
    }

    public function test_payroll_cycle_is_gap_free_for_last()
    {
        $cycle = $this->service()->forPayrollDay('last', 5, 2026);

        $this->assertEquals('2026-04-30 00:00:00', $cycle['start']->toDateTimeString());
        $this->assertEquals('2026-05-30 23:59:59', $cycle['end']->toDateTimeString());
        $this->assertEquals('2026-05-31 00:00:00', $cycle['next_start']->toDateTimeString());
        $this->assertEquals(1, $cycle['next_start']->diffInSeconds($cycle['end']));
    }

    public function test_payroll_cycle_across_all_days_and_months()
    {
        $payrollDays = ['last', '1', '5', '15', '16', '25', '28', '30', '31'];
        $months = range(1, 12);
        $year = 2026;
        $service = $this->service();

        foreach ($payrollDays as $payrollDay) {
            foreach ($months as $month) {
                $cycle = $service->forPayrollDay($payrollDay, $month, $year);

                $diff = $cycle['next_start']->diffInSeconds($cycle['end']);
                $this->assertEquals(1, $diff, "Gap detected for payroll day: {$payrollDay} in month: {$month}");
                $this->assertTrue($cycle['start']->lt($cycle['end']), "Start date is not before end date for payroll day: {$payrollDay} in month: {$month}");
            }
        }
    }

    /**
     * Cross-check against tests/Unit/PayrollCycleTest.php's independent inline
     * copy of the same algorithm, for every payroll day/month combination.
     */
    public function test_matches_the_legacy_inline_copy_byte_for_byte()
    {
        $payrollCycleTest = new PayrollCycleTest('test_payroll_cycle_is_gap_free_for_last');
        $reference = new \ReflectionMethod($payrollCycleTest, 'calculateCycleDates');
        $reference->setAccessible(true);

        $payrollDays = ['last', '1', '5', '15', '16', '25', '28', '30', '31'];
        $service = $this->service();

        foreach ($payrollDays as $payrollDay) {
            foreach (range(1, 12) as $month) {
                $expected = $reference->invoke($payrollCycleTest, 2026, $month, $payrollDay);
                $actual = $service->forPayrollDay($payrollDay, $month, 2026);

                $this->assertEquals(
                    $expected['start']->toDateTimeString(),
                    $actual['start']->toDateTimeString(),
                    "start mismatch for payroll day {$payrollDay}, month {$month}"
                );
                $this->assertEquals(
                    $expected['end']->toDateTimeString(),
                    $actual['end']->toDateTimeString(),
                    "end mismatch for payroll day {$payrollDay}, month {$month}"
                );
            }
        }
    }
}
