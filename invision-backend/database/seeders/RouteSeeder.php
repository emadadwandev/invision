<?php

namespace Database\Seeders;

use App\Enums\RouteStatus;
use App\Enums\VisitFrequency;
use App\Enums\VisitStatus;
use App\Models\RoutePlan;
use App\Models\RouteInstance;
use App\Models\Store;
use App\Models\StoreVisit;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'invision-default')->first();

        if (! $tenant) {
            return;
        }

        $stores = Store::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $fieldUsers = User::where('tenant_id', $tenant->id)
            ->whereIn('role', ['field_force', 'promoter', 'merchandiser', 'sales_representative'])
            ->where('is_active', true)
            ->get();

        // If no field users, use the first available non-admin user or admin
        if ($fieldUsers->isEmpty()) {
            $fieldUsers = User::where('tenant_id', $tenant->id)->where('is_active', true)->take(2)->get();
        }

        if ($fieldUsers->isEmpty() || $stores->count() < 2) {
            return;
        }

        // --- Route Plan 1: Beirut Morning Route ---
        $user1 = $fieldUsers->first();
        $plan1 = RoutePlan::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Beirut Morning Route'],
            [
                'description' => 'Daily morning route covering Hamra, Verdun, and Achrafieh stores.',
                'assigned_to' => $user1->id,
                'frequency' => VisitFrequency::Daily,
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek()->addWeeks(3),
                'status' => RouteStatus::Published,
                'total_stores' => 0,
            ]
        );

        if ($plan1->wasRecentlyCreated) {
            $planStores1 = $stores->take(3);
            foreach ($planStores1->values() as $i => $store) {
                $plan1->routeStores()->create([
                    'store_id' => $store->id,
                    'visit_order' => $i + 1,
                    'expected_duration_minutes' => rand(15, 45),
                ]);
            }
            $plan1->recalculateTotalStores();
        }

        // --- Route Plan 2: Metn & Jounieh Route ---
        $user2 = $fieldUsers->count() > 1 ? $fieldUsers->last() : $user1;
        $plan2 = RoutePlan::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Metn & Jounieh Route'],
            [
                'description' => 'Weekly route covering Dora and Jounieh area stores.',
                'assigned_to' => $user2->id,
                'frequency' => VisitFrequency::Weekly,
                'start_date' => now()->startOfWeek(),
                'end_date' => null,
                'status' => RouteStatus::Published,
                'total_stores' => 0,
            ]
        );

        if ($plan2->wasRecentlyCreated && $stores->count() >= 4) {
            $planStores2 = $stores->slice(2)->take(3);
            foreach ($planStores2->values() as $i => $store) {
                $plan2->routeStores()->create([
                    'store_id' => $store->id,
                    'visit_order' => $i + 1,
                    'expected_duration_minutes' => rand(20, 60),
                ]);
            }
            $plan2->recalculateTotalStores();
        }

        // --- Route Plan 3: Draft route ---
        RoutePlan::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'South Beirut Expansion'],
            [
                'description' => 'New route being planned for southern Beirut suburbs.',
                'assigned_to' => $user1->id,
                'frequency' => VisitFrequency::BiWeekly,
                'start_date' => now()->addWeek(),
                'status' => RouteStatus::Draft,
                'total_stores' => 0,
            ]
        );

        // --- Create a sample route instance for today (Plan 1) ---
        $instance = RouteInstance::query()->firstOrCreate(
            ['route_plan_id' => $plan1->id, 'user_id' => $user1->id, 'route_date' => now()->toDateString()],
            [
                'tenant_id' => $tenant->id,
                'status' => RouteStatus::InProgress,
                'started_at' => now()->setHour(8)->setMinute(30),
                'total_visits' => $plan1->total_stores,
                'completed_visits' => 0,
            ]
        );

        if ($instance->wasRecentlyCreated) {
            foreach ($plan1->routeStores as $rs) {
                $isFirst = $rs->visit_order === 1;

                StoreVisit::create([
                    'tenant_id' => $tenant->id,
                    'route_instance_id' => $instance->id,
                    'store_id' => $rs->store_id,
                    'user_id' => $user1->id,
                    'visit_order' => $rs->visit_order,
                    'status' => $isFirst ? VisitStatus::Completed : VisitStatus::Pending,
                    'checked_in_at' => $isFirst ? now()->setHour(8)->setMinute(35) : null,
                    'checkin_latitude' => $isFirst ? $rs->store->gps_latitude : null,
                    'checkin_longitude' => $isFirst ? $rs->store->gps_longitude : null,
                    'checkin_distance_meters' => $isFirst ? rand(1, 4) : null,
                    'checked_out_at' => $isFirst ? now()->setHour(9)->setMinute(10) : null,
                    'checkout_latitude' => $isFirst ? $rs->store->gps_latitude : null,
                    'checkout_longitude' => $isFirst ? $rs->store->gps_longitude : null,
                    'duration_minutes' => $isFirst ? 35 : null,
                ]);
            }

            $completedCount = StoreVisit::where('route_instance_id', $instance->id)
                ->where('status', VisitStatus::Completed)
                ->count();
            $instance->update(['completed_visits' => $completedCount]);
        }
    }
}
