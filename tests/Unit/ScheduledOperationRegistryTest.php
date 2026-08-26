<?php

namespace Tests\Unit;

use App\Scheduled\ScheduledOperationRegistry;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ScheduledOperationRegistryTest extends TestCase
{
    public function test_every_operation_has_a_unique_key_and_callable_handler(): void
    {
        $definitions = (new ScheduledOperationRegistry())->all();
        $keys = array_column($definitions, 'key');

        $this->assertCount(count(array_unique($keys)), $keys);

        foreach ($definitions as $definition) {
            [$class, $method] = $definition['handler'];
            $this->assertTrue(method_exists($class, $method), "Missing handler {$class}::{$method}");
        }
    }

    public function test_weekly_and_hourly_schedules_only_match_their_exact_minute(): void
    {
        $registry = new ScheduledOperationRegistry();
        $mondayReport = $registry->find('report.jobstart');
        $hourlyTask = $registry->find('hourly.client_enquiry_followup');

        $this->assertTrue($registry->isDue($mondayReport, Carbon::parse('2026-08-24 00:05:00')));
        $this->assertFalse($registry->isDue($mondayReport, Carbon::parse('2026-08-25 00:05:00')));
        $this->assertFalse($registry->isDue($mondayReport, Carbon::parse('2026-08-24 00:06:00')));
        $this->assertTrue($registry->isDue($hourlyTask, Carbon::parse('2026-08-24 13:01:00')));
        $this->assertFalse($registry->isDue($hourlyTask, Carbon::parse('2026-08-24 13:02:00')));
    }

    public function test_fortnightly_and_monthly_rules_match_expected_dates(): void
    {
        $registry = new ScheduledOperationRegistry();

        $this->assertTrue($registry->isDue($registry->find('report.fortnightly'), Carbon::parse('2026-08-24 00:05:00')));
        $this->assertFalse($registry->isDue($registry->find('report.fortnightly'), Carbon::parse('2026-08-31 00:05:00')));
        $this->assertTrue($registry->isDue($registry->find('report.old_users'), Carbon::parse('2026-09-01 00:05:00')));
        $this->assertTrue($registry->isDue($registry->find('report.outstanding_aftercare'), Carbon::parse('2026-08-28 00:05:00')));
        $this->assertTrue($registry->isDue($registry->find('report.trades_attendance'), Carbon::parse('2026-09-01 00:05:00')));
    }
}
