<?php

namespace Tests\Unit;

use App\Enums\VisitFrequency;
use App\Models\RoutePlan;
use App\Models\RoutePlanStore;
use Tests\TestCase;

class RoutePlanScheduleTest extends TestCase
{
    private function plan(VisitFrequency $frequency, array $rows, ?array $workDays = null): RoutePlan
    {
        $plan = new RoutePlan(['frequency' => $frequency, 'start_date' => '2026-11-02', 'work_days' => $workDays]); // a Monday
        $plan->setRelation('routeStores', collect($rows)->map(fn ($r, $i) => new RoutePlanStore($r + ['visit_order' => $i + 1])));

        return $plan;
    }

    private function storeIds(RoutePlan $plan, string $date): array
    {
        return $plan->storesForDate($date)->pluck('store_id')->all();
    }

    public function test_weekly_repeats_by_weekday(): void
    {
        $plan = $this->plan(VisitFrequency::Weekly, [
            ['store_id' => 1, 'day_of_week' => 1],
            ['store_id' => 2, 'day_of_week' => 2],
        ]);

        $this->assertSame([1], $this->storeIds($plan, '2026-11-02'));
        $this->assertSame([2], $this->storeIds($plan, '2026-11-03'));
        $this->assertSame([1], $this->storeIds($plan, '2026-11-09')); // next Monday
        $this->assertSame([], $this->storeIds($plan, '2026-11-01')); // before start
    }

    public function test_daily_uses_work_days(): void
    {
        $plan = $this->plan(VisitFrequency::Daily, [['store_id' => 1], ['store_id' => 2]], [7, 1, 2, 3, 4]);

        $this->assertSame([1, 2], $this->storeIds($plan, '2026-11-02'));
        $this->assertSame([], $this->storeIds($plan, '2026-11-06')); // Friday off
    }

    public function test_monthly_rotates_four_weeks(): void
    {
        $plan = $this->plan(VisitFrequency::Monthly, [
            ['store_id' => 1, 'day_of_week' => 1, 'week_number' => 1],
            ['store_id' => 2, 'day_of_week' => 1, 'week_number' => 2],
            ['store_id' => 3, 'day_of_week' => 1],
        ]);

        $this->assertSame([1, 3], $this->storeIds($plan, '2026-11-02'));
        $this->assertSame([2, 3], $this->storeIds($plan, '2026-11-09'));
        $this->assertSame([3], $this->storeIds($plan, '2026-11-16'));
        $this->assertSame([1, 3], $this->storeIds($plan, '2026-11-30')); // cycle restarts
    }

    public function test_specific_week_overrides_template(): void
    {
        $plan = $this->plan(VisitFrequency::Weekly, [
            ['store_id' => 1, 'day_of_week' => 1],
            ['store_id' => 9, 'day_of_week' => 3, 'week_start' => '2026-11-09'],
        ]);

        $this->assertSame([], $this->storeIds($plan, '2026-11-09')); // template replaced that week
        $this->assertSame([9], $this->storeIds($plan, '2026-11-11'));
        $this->assertSame([1], $this->storeIds($plan, '2026-11-16')); // back to normal
    }
}
