<?php

namespace App\Services;

use App\Enums\RouteStatus;
use App\Enums\VisitStatus;
use App\Events\GpsPositionUpdated;
use App\Events\VisitStatusChanged;
use App\Models\GpsTrackingLog;
use App\Models\RouteInstance;
use App\Models\RoutePlan;
use App\Models\RoutePlanStore;
use App\Models\StoreVisit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class RouteService
{
    // ─── Route Plans ──────────────────────────────────────────

    public function listPlans(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = RoutePlan::with(['assignedUser', 'routeStores.store']);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        if (! empty($filters['assigned_to_roles'])) {
            $query->whereHas('assignedUser', function ($q) use ($filters) {
                $q->whereIn('role', $filters['assigned_to_roles']);
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function createPlan(array $data): RoutePlan
    {
        $stores = $data['stores'] ?? [];
        unset($data['stores']);

        $plan = RoutePlan::create($data);

        foreach ($stores as $i => $storeData) {
            $plan->routeStores()->create([
                'store_id' => $storeData['store_id'],
                'visit_order' => $storeData['visit_order'] ?? ($i + 1),
                'expected_duration_minutes' => $storeData['expected_duration_minutes'] ?? null,
                'notes' => $storeData['notes'] ?? null,
            ]);
        }

        $plan->recalculateTotalStores();

        return $plan->load(['assignedUser', 'routeStores.store']);
    }

    public function updatePlan(RoutePlan $plan, array $data): RoutePlan
    {
        $stores = $data['stores'] ?? null;
        unset($data['stores']);

        $plan->update($data);

        if ($stores !== null) {
            $plan->routeStores()->delete();

            foreach ($stores as $i => $storeData) {
                $plan->routeStores()->create([
                    'store_id' => $storeData['store_id'],
                    'visit_order' => $storeData['visit_order'] ?? ($i + 1),
                    'expected_duration_minutes' => $storeData['expected_duration_minutes'] ?? null,
                    'notes' => $storeData['notes'] ?? null,
                ]);
            }

            $plan->recalculateTotalStores();
        }

        return $plan->load(['assignedUser', 'routeStores.store']);
    }

    public function deletePlan(RoutePlan $plan): void
    {
        $plan->delete();
    }

    public function addStoreToPlan(RoutePlan $plan, array $data): RoutePlanStore
    {
        $maxOrder = $plan->routeStores()->max('visit_order') ?? 0;

        $routeStore = $plan->routeStores()->create([
            'store_id' => $data['store_id'],
            'visit_order' => $data['visit_order'] ?? ($maxOrder + 1),
            'expected_duration_minutes' => $data['expected_duration_minutes'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $plan->recalculateTotalStores();

        return $routeStore->load('store');
    }

    public function removeStoreFromPlan(RoutePlan $plan, int $storeId): void
    {
        $plan->routeStores()->where('store_id', $storeId)->delete();
        $plan->recalculateTotalStores();
    }

    public function reorderPlanStores(RoutePlan $plan, array $storeOrders): void
    {
        foreach ($storeOrders as $order) {
            RoutePlanStore::where('route_plan_id', $plan->id)
                ->where('store_id', $order['store_id'])
                ->update(['visit_order' => $order['visit_order']]);
        }
    }

    // ─── Route Instances ──────────────────────────────────────

    public function listInstances(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = RouteInstance::with(['routePlan', 'user', 'visits.store']);

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['route_date'])) {
            $query->whereDate('route_date', $filters['route_date']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('route_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('route_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['route_plan_id'])) {
            $query->where('route_plan_id', $filters['route_plan_id']);
        }

        return $query->latest('route_date')->paginate($perPage);
    }

    public function createInstance(RoutePlan $plan, string $date): RouteInstance
    {
        $existing = RouteInstance::where('route_plan_id', $plan->id)
            ->where('user_id', $plan->assigned_to)
            ->whereDate('route_date', $date)
            ->first();

        if ($existing) {
            $this->syncInstanceVisits($existing, $plan);
            return $existing->load(['routePlan', 'visits.store']);
        }

        $instance = RouteInstance::create([
            'tenant_id' => $plan->tenant_id,
            'route_plan_id' => $plan->id,
            'user_id' => $plan->assigned_to,
            'route_date' => $date,
            'status' => RouteStatus::Published,
            'total_visits' => $plan->total_stores,
        ]);

        foreach ($plan->routeStores as $routeStore) {
            $instance->visits()->create([
                'tenant_id' => $plan->tenant_id,
                'store_id' => $routeStore->store_id,
                'user_id' => $plan->assigned_to,
                'visit_order' => $routeStore->visit_order,
                'status' => VisitStatus::Pending,
            ]);
        }

        return $instance->load(['routePlan', 'visits.store']);
    }

    /**
     * Sync an existing route instance's visits to match the current plan stores.
     * Preserves visits that are already checked-in, completed, or skipped.
     * Removes pending visits for stores removed from the plan.
     * Adds new pending visits for stores added to the plan.
     */
    private function syncInstanceVisits(RouteInstance $instance, RoutePlan $plan): void
    {
        $plan->loadMissing('routeStores');
        $planStoreIds = $plan->routeStores->pluck('store_id');

        // Remove pending visits for stores no longer in the plan
        $instance->visits()
            ->where('status', VisitStatus::Pending)
            ->whereNotIn('store_id', $planStoreIds)
            ->delete();

        // Determine which stores already have a visit (any status)
        $existingStoreIds = $instance->visits()->pluck('store_id');

        // Add visits for new stores not yet in this instance
        foreach ($plan->routeStores as $routeStore) {
            if ($existingStoreIds->contains($routeStore->store_id)) {
                // Update visit order in case it changed
                $instance->visits()
                    ->where('store_id', $routeStore->store_id)
                    ->update(['visit_order' => $routeStore->visit_order]);
            } else {
                $instance->visits()->create([
                    'tenant_id' => $plan->tenant_id,
                    'store_id' => $routeStore->store_id,
                    'user_id' => $plan->assigned_to,
                    'visit_order' => $routeStore->visit_order,
                    'status' => VisitStatus::Pending,
                ]);
            }
        }

        // Recalculate total_visits to reflect current visit count
        $instance->update(['total_visits' => $instance->visits()->count()]);
    }

    public function startRoute(RouteInstance $instance): RouteInstance
    {
        $instance->update([
            'status' => RouteStatus::InProgress,
            'started_at' => now(),
        ]);

        return $instance;
    }

    public function completeRoute(RouteInstance $instance): RouteInstance
    {
        $instance->update([
            'status' => RouteStatus::Completed,
            'completed_at' => now(),
        ]);

        return $instance;
    }

    // ─── Store Visits ─────────────────────────────────────────

    public function checkIn(StoreVisit $visit, array $data): StoreVisit
    {
        $store = $visit->store;
        $distance = null;

        $storeHasGps = $store->gps_latitude && $store->gps_longitude;
        $userHasGps = isset($data['latitude'], $data['longitude']);

        if ($storeHasGps && $userHasGps) {
            $distance = GeoFenceService::calculateDistance(
                (float) $data['latitude'],
                (float) $data['longitude'],
                (float) $store->gps_latitude,
                (float) $store->gps_longitude,
            );

            $geoFenceService = app(GeoFenceService::class);
            $settings = $geoFenceService->getSettings();

            if ($settings->enforce_geofence && $distance > $settings->checkin_radius_meters) {
                throw new \App\Exceptions\GeoFenceException(
                    "You are " . round($distance) . "m away from the store. Check-in requires being within {$settings->checkin_radius_meters}m.",
                    $distance,
                    $settings->checkin_radius_meters
                );
            }
        } elseif ($storeHasGps && ! $userHasGps) {
            // Store has GPS but user didn't send coordinates — only block if GPS is required
            $geoFenceService = app(GeoFenceService::class);
            $settings = $geoFenceService->getSettings();

            if ($settings->require_gps_for_checkin) {
                throw new \App\Exceptions\GeoFenceException(
                    'GPS coordinates are required for check-in.',
                    null,
                    $settings->checkin_radius_meters
                );
            }
        }
        // If store has no GPS coordinates, geofence cannot be enforced — allow check-in

        $visit->update([
            'status' => VisitStatus::CheckedIn,
            'checked_in_at' => now(),
            'checkin_latitude' => $data['latitude'] ?? null,
            'checkin_longitude' => $data['longitude'] ?? null,
            'checkin_qr_code' => $data['qr_code'] ?? null,
            'checkin_distance_meters' => $distance,
        ]);

        // Start route instance if not started yet
        $instance = $visit->routeInstance;
        if ($instance->status === RouteStatus::Published) {
            $this->startRoute($instance);
        }

        // Broadcast visit status change
        VisitStatusChanged::dispatch(
            $visit->tenant_id ?? $instance->tenant_id,
            $instance->id,
            $visit->id,
            $instance->user_id,
            VisitStatus::CheckedIn->value,
            $visit->store_id,
            $store->name ?? null,
        );

        return $visit;
    }

    public function checkOut(StoreVisit $visit, array $data): StoreVisit
    {
        $duration = null;
        if ($visit->checked_in_at) {
            $duration = (int) $visit->checked_in_at->diffInMinutes(now());
        }

        $visit->update([
            'status' => VisitStatus::Completed,
            'checked_out_at' => now(),
            'checkout_latitude' => $data['latitude'] ?? null,
            'checkout_longitude' => $data['longitude'] ?? null,
            'duration_minutes' => $duration,
            'notes' => $data['notes'] ?? $visit->notes,
        ]);

        // Update instance completed count
        $instance = $visit->routeInstance;
        $completedCount = $instance->visits()->where('status', VisitStatus::Completed)->count();
        $instance->update(['completed_visits' => $completedCount]);

        // Auto-complete route if all visits done
        $totalVisits = $instance->total_visits;
        $skipped = $instance->visits()->where('status', VisitStatus::Skipped)->count();
        if (($completedCount + $skipped) >= $totalVisits) {
            $this->completeRoute($instance);
        }

        // Broadcast visit status change
        VisitStatusChanged::dispatch(
            $visit->tenant_id ?? $instance->tenant_id,
            $instance->id,
            $visit->id,
            $instance->user_id,
            VisitStatus::Completed->value,
            $visit->store_id,
        );

        return $visit;
    }

    public function skipVisit(StoreVisit $visit, string $reason): StoreVisit
    {
        $visit->update([
            'status' => VisitStatus::Skipped,
            'skip_reason' => $reason,
        ]);

        // Update instance counts
        $instance = $visit->routeInstance;
        $completedCount = $instance->visits()->where('status', VisitStatus::Completed)->count();
        $skippedCount = $instance->visits()->where('status', VisitStatus::Skipped)->count();
        $instance->update(['completed_visits' => $completedCount]);

        if (($completedCount + $skippedCount) >= $instance->total_visits) {
            $this->completeRoute($instance);
        }

        return $visit;
    }

    // ─── GPS Tracking ─────────────────────────────────────────

    public function logGps(array $data): GpsTrackingLog
    {
        $log = GpsTrackingLog::create([
            'tenant_id' => $data['tenant_id'],
            'user_id' => $data['user_id'],
            'route_instance_id' => $data['route_instance_id'] ?? null,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy_meters' => $data['accuracy_meters'] ?? null,
            'speed_kmh' => $data['speed_kmh'] ?? null,
            'bearing' => $data['bearing'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);

        // Broadcast GPS position update for live tracking
        $user = $log->user;
        if ($user) {
            GpsPositionUpdated::dispatch(
                $data['tenant_id'],
                $data['user_id'],
                $user->full_name ?? '',
                $user->role?->value ?? '',
                (float) $data['latitude'],
                (float) $data['longitude'],
                isset($data['speed_kmh']) ? (float) $data['speed_kmh'] : null,
                $data['route_instance_id'] ?? null,
            );
        }

        return $log;
    }

    public function batchLogGps(array $logs): int
    {
        $records = collect($logs)->map(fn (array $log) => [
            'tenant_id' => $log['tenant_id'],
            'user_id' => $log['user_id'],
            'route_instance_id' => $log['route_instance_id'] ?? null,
            'latitude' => $log['latitude'],
            'longitude' => $log['longitude'],
            'accuracy_meters' => $log['accuracy_meters'] ?? null,
            'speed_kmh' => $log['speed_kmh'] ?? null,
            'bearing' => $log['bearing'] ?? null,
            'recorded_at' => $log['recorded_at'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if (empty($records)) {
            return 0;
        }

        GpsTrackingLog::insert($records);
        $count = count($records);

        // Broadcast the latest position per user for real-time command center updates
        collect($records)
            ->groupBy('user_id')
            ->each(function ($userLogs, $userId) {
                $latest = collect($userLogs)->last();
                $user = User::find($userId);
                if ($user) {
                    GpsPositionUpdated::dispatch(
                        (int) $latest['tenant_id'],
                        (int) $userId,
                        $user->full_name ?? '',
                        $user->role?->value ?? '',
                        (float) $latest['latitude'],
                        (float) $latest['longitude'],
                        isset($latest['speed_kmh']) ? (float) $latest['speed_kmh'] : null,
                        $latest['route_instance_id'] ?? null,
                    );
                }
            });

        return $count;
    }

    public function getUserTrackingLogs(int $userId, string $date): \Illuminate\Database\Eloquent\Collection
    {
        return GpsTrackingLog::where('user_id', $userId)
            ->whereDate('recorded_at', $date)
            ->orderBy('recorded_at')
            ->get();
    }
}
