<?php

namespace Tests\Unit;

use App\Models\Site\Planner\PublicHoliday;
use App\Models\Site\Planner\PlannerClosure;
use App\Models\Misc\PublicHoliday as ContractPublicHoliday;
use App\Services\Planner\PlannerDateService;
use App\Services\Planner\PlannerCalendarChangeService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PlannerNonWorkingDatesTest extends TestCase
{
    private function entry(string $name, string $date, ?string $end = null, int $status = 1): object
    {
        return (object) [
            'name' => $name, 'date' => Carbon::parse($date),
            'end_date' => $end ? Carbon::parse($end) : null, 'status' => $status,
        ];
    }

    public function test_legacy_single_day_and_inclusive_cross_year_range(): void
    {
        $dates = PublicHoliday::expandDates([
            $this->entry('Christmas', '2026-12-25'),
            $this->entry('Shutdown', '2026-12-30', '2027-01-04'),
        ]);
        $this->assertCount(7, $dates);
        $this->assertSame('Christmas', $dates['2026-12-25']);
        $this->assertSame('Shutdown', $dates['2027-01-04']);
        $this->assertArrayNotHasKey('2027-01-05', $dates);
    }

    public function test_overlap_has_one_blocked_date_and_combines_unique_labels(): void
    {
        $dates = PublicHoliday::expandDates([
            $this->entry('Shutdown', '2026-12-24', '2026-12-28'),
            $this->entry('Christmas', '2026-12-25'),
            $this->entry('Christmas', '2026-12-25'),
            $this->entry('Inactive', '2026-12-29', null, 0),
        ]);
        $this->assertCount(5, $dates);
        $this->assertSame('Shutdown / Christmas', $dates['2026-12-25']);
        $this->assertArrayNotHasKey('2026-12-29', $dates);
    }

    public function test_duration_skips_shutdown_weekends_and_new_year(): void
    {
        $dates = (new PlannerDateService())->withHolidays(PublicHoliday::expandDates([
            $this->entry('Shutdown', '2026-12-24', '2027-01-03'),
            $this->entry('New Year', '2027-01-01'),
        ]));
        $this->assertSame('2027-01-06', $dates->endDate('2026-12-22', 5)->toDateString());
        $this->assertSame(5, $dates->workDaysBetween('2026-12-22', '2027-01-06'));
        $this->assertSame('2026-12-23', $dates->shift('2027-01-04', -1)->toDateString());
        $this->assertFalse($dates->isWorkDay('2026-12-28'));
        $this->assertTrue($dates->isWorkDay('2027-01-04'));
    }

    public function test_weekend_override_can_distinguish_weekends_from_closures(): void
    {
        $dates = (new PlannerDateService())->withHolidays(['2026-12-26' => 'Shutdown']);
        $this->assertTrue($dates->isBlocked('2026-12-26'));
        $this->assertFalse($dates->isBlocked('2027-01-09'));
        $this->assertFalse($dates->isWorkDay('2027-01-09'));
    }

    public function test_preview_calendar_does_not_change_live_date_service(): void
    {
        $live = (new PlannerDateService())->withHolidays([]);
        $preview = $live->withHolidays(['2026-12-24' => 'Shutdown']);
        $this->assertTrue($live->isWorkDay('2026-12-24'));
        $this->assertFalse($preview->isWorkDay('2026-12-24'));
    }

    public function test_reopening_a_day_shortens_finish_but_keeps_duration(): void
    {
        $closed = (new PlannerDateService())->withHolidays(['2026-12-23' => 'Closure']);
        $open = $closed->withHolidays([]);
        $this->assertSame('2026-12-24', $closed->endDate('2026-12-21', 3)->toDateString());
        $this->assertSame('2026-12-23', $open->endDate('2026-12-21', 3)->toDateString());
        $this->assertSame(3, $closed->workDaysBetween('2026-12-21', '2026-12-24'));
        $this->assertSame(3, $open->workDaysBetween('2026-12-21', '2026-12-23'));
    }

    public function test_forward_and_backward_shifts_do_not_mutate_the_supplied_date(): void
    {
        $dates = (new PlannerDateService())->withHolidays(['2026-12-25' => 'Christmas']);
        $input = Carbon::parse('2026-12-24');
        $this->assertSame('2026-12-28', $dates->shift($input, 1)->toDateString());
        $this->assertSame('2026-12-24', $input->toDateString());
        $this->assertSame('2026-12-24', $dates->shift('2026-12-28', -1)->toDateString());
        $this->assertSame('2026-12-24', $dates->shift($input, 0)->toDateString());
    }

    private function plannerRow(int $id, string $from, int $days = 1, array $overrides = []): array
    {
        $dates = (new PlannerDateService())->withHolidays($this->oldCalendar());

        return array_merge([
            'id' => $id, 'site_id' => 1, 'entity_type' => 'c', 'entity_id' => 99,
            'task_id' => 50, 'from' => $from, 'to' => $dates->endDate($from, $days)->toDateString(),
            'days' => $days, 'weekend' => false,
            'site' => '8275-Kanagasabai-Denistone', 'task' => 'GF Waterproof Inspection',
        ], $overrides);
    }

    private function oldCalendar(): array
    {
        return [
            '2026-12-25' => 'Christmas', '2026-12-28' => 'Boxing Day',
            '2027-01-01' => 'New Year', '2027-01-26' => 'Australia Day',
        ];
    }

    private function shutdownCalendar(): array
    {
        return array_merge($this->oldCalendar(), PublicHoliday::expandDates([
            $this->entry('Shutdown', '2026-12-21', '2027-01-11'),
        ]));
    }

    private function reflowRows(array $rows, ?array $old = null, ?array $new = null): array
    {
        return (new PlannerCalendarChangeService(new PlannerDateService()))->reflow(
            $rows, $old ?? $this->oldCalendar(), $new ?? $this->shutdownCalendar(), '2026-10-21',
        );
    }

    public function test_reflow_preserves_spacing_and_moves_tasks_already_after_shutdown(): void
    {
        $rows = [
            $this->plannerRow(1, '2026-12-21'),
            $this->plannerRow(2, '2026-12-22'),
            $this->plannerRow(3, '2027-01-12'),
        ];
        $result = $this->reflowRows($rows);
        $this->assertSame([], $result['blockers']);
        $byId = array_column($result['changes'], null, 'id');
        $this->assertSame('2027-01-12', $byId[1]['new_from']);
        $this->assertSame('2027-01-13', $byId[2]['new_from']);
        $this->assertSame('2027-02-01', $byId[3]['new_from']);
        $old = (new PlannerDateService())->withHolidays($this->oldCalendar());
        $new = $old->withHolidays($this->shutdownCalendar());
        foreach ($rows as $row) {
            $this->assertSame(
                $old->workDaysBetween($rows[0]['from'], $row['from']),
                $new->workDaysBetween($byId[1]['new_from'], $byId[$row['id']]['new_from']),
            );
            $this->assertSame($row['days'], $byId[$row['id']]['days']);
        }
    }

    public function test_site_anchors_are_independent_and_unaffected_sites_do_not_move(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(1, '2026-12-21'),
            $this->plannerRow(2, '2027-01-06', 1, ['site_id' => 2, 'site' => 'Second site']),
            $this->plannerRow(3, '2027-01-20', 1, ['site_id' => 3, 'site' => 'Unchanged site']),
        ]);
        $this->assertSame([], $result['blockers']);
        $byId = array_column($result['changes'], null, 'id');
        $this->assertSame('2027-01-12', $byId[1]['new_from']);
        $this->assertSame('2027-01-12', $byId[2]['new_from']);
        $this->assertArrayNotHasKey(3, $byId);
    }

    public function test_crossing_task_keeps_start_and_preserves_three_working_days(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(1, '2026-12-17', 3),
            $this->plannerRow(2, '2027-01-12'),
        ]);
        $this->assertSame([], $result['blockers']);
        $byId = array_column($result['changes'], null, 'id');
        $this->assertSame('2026-12-17', $byId[1]['new_from']);
        $this->assertSame('2027-01-12', $byId[1]['new_to']);
        $this->assertSame(3, $byId[1]['days']);
        $this->assertGreaterThan('2027-01-12', $byId[2]['new_from']);
    }

    public function test_same_day_parallel_tasks_remain_parallel(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(1, '2026-12-21'),
            $this->plannerRow(2, '2026-12-21', 1, ['task_id' => 51, 'task' => 'Laundry']),
        ]);
        $this->assertSame([], $result['blockers']);
        $this->assertCount(2, $result['changes']);
        $this->assertSame($result['changes'][0]['new_from'], $result['changes'][1]['new_from']);
    }

    public function test_cut_out_entries_do_not_collapse_onto_one_reopening_day(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(149472, '2026-12-29', 1, ['task' => 'Cut Out GF']),
            $this->plannerRow(149473, '2027-01-06', 1, ['task' => 'Cut Out GF']),
        ]);
        $this->assertSame([], $result['blockers']);
        $byId = array_column($result['changes'], null, 'id');
        $this->assertSame('2027-01-12', $byId[149472]['new_from']);
        $this->assertSame('2027-01-19', $byId[149473]['new_from']);
    }

    public function test_real_overlap_warning_names_the_site_task_ids_and_dates(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(149459, '2026-12-21', 2),
            $this->plannerRow(149464, '2026-12-22'),
        ]);
        $this->assertCount(1, $result['blockers']);
        $message = $result['blockers'][0];
        foreach (['8275-Kanagasabai-Denistone', 'GF Waterproof Inspection', '#149459', '#149464', '13/01/2027'] as $part) {
            $this->assertStringContainsString($part, $message);
        }
    }

    public function test_remaining_christmas_placeholder_is_named_and_blocked_not_rescheduled(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(155747, '2026-12-21', 14, [
                'task_id' => 403, 'entity_type' => 't', 'entity_id' => 45,
                'site' => '8276-Carr-Coogee', 'task' => 'Christmas Holidays',
            ]),
        ]);
        $this->assertCount(1, $result['blockers']);
        $this->assertStringContainsString('8276-Carr-Coogee', $result['blockers'][0]);
        $this->assertStringContainsString('Christmas Holidays', $result['blockers'][0]);
        $this->assertStringContainsString('#155747', $result['blockers'][0]);
        $this->assertSame('2026-12-21', $result['changes'][0]['new_from']);
    }

    public function test_weekend_only_calendar_change_does_not_move_weekday_work(): void
    {
        $row = $this->plannerRow(1, '2026-11-06', 2);
        $result = $this->reflowRows([$row], [], ['2026-11-07' => 'Saturday closure']);
        $this->assertSame([], $result['changes']);
        $this->assertSame([], $result['blockers']);
    }

    public function test_renaming_an_existing_closure_does_not_shift_the_schedule(): void
    {
        $result = $this->reflowRows([$this->plannerRow(1, '2027-01-12')],
            ['2027-01-11' => 'Old label'], ['2027-01-11' => 'New label']);
        $this->assertSame([], $result['changes']);
        $this->assertSame([], $result['blockers']);
    }

    public function test_removal_reflows_a_crossing_task_and_its_later_tasks(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(1, '2026-12-22', 3, ['to' => '2026-12-29']),
            $this->plannerRow(2, '2026-12-30'),
        ], array_merge($this->oldCalendar(), ['2026-12-23' => 'Closure']), $this->oldCalendar());
        $this->assertSame([], $result['blockers']);
        $byId = array_column($result['changes'], null, 'id');
        $this->assertSame('2026-12-24', $byId[1]['new_to']);
        $this->assertSame('2026-12-29', $byId[2]['new_from']);
    }

    public function test_pre_anchor_work_and_completed_history_remain_unchanged(): void
    {
        $result = $this->reflowRows([
            $this->plannerRow(1, '2026-09-01'),
            $this->plannerRow(2, '2026-12-10'),
            $this->plannerRow(3, '2026-12-21'),
        ]);
        $byId = array_column($result['changes'], null, 'id');
        $this->assertArrayNotHasKey(1, $byId);
        $this->assertArrayNotHasKey(2, $byId);
        $this->assertSame('2027-01-12', $byId[3]['new_from']);
    }

    public function test_planner_closures_use_a_separate_table_from_contract_public_holidays(): void
    {
        // No database or application bootstrap is needed to inspect table routing.
        $public = (new \ReflectionClass(PublicHoliday::class))->newInstanceWithoutConstructor();
        $closure = (new \ReflectionClass(PlannerClosure::class))->newInstanceWithoutConstructor();
        $contract = (new \ReflectionClass(ContractPublicHoliday::class))->newInstanceWithoutConstructor();
        $this->assertSame('public_holidays', $contract->getTable());
        $this->assertSame('public_holidays', $public->getTable());
        $this->assertSame('planner_closures', $closure->getTable());
        $this->assertNotSame($public->getTable(), $closure->getTable());
    }

    public function test_planner_combines_both_calendars_without_changing_public_holiday_input(): void
    {
        $public = ['2026-12-25' => 'Christmas Day'];
        $closures = ['2026-12-24' => 'Company shutdown', '2026-12-25' => 'Company shutdown'];
        $service = new PlannerDateService();
        $combined = $service->mergeCalendars($public, $closures);
        $this->assertSame(['2026-12-25' => 'Christmas Day'], $public);
        $this->assertArrayNotHasKey('2026-12-24', $public);
        $this->assertSame('Company shutdown', $combined['2026-12-24']);
        $this->assertSame('Christmas Day / Company shutdown', $combined['2026-12-25']);
        $this->assertFalse($service->withHolidays($combined)->isWorkDay('2026-12-24'));
    }
}
