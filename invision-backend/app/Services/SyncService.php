<?php

namespace App\Services;

use App\Models\SyncQueueItem;
use App\Models\SyncToken;
use App\Models\Store;
use App\Models\Product;
use App\Models\RoutePlan;
use App\Models\Campaign;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncService
{
    /**
     * Entities available for delta pull sync.
     * Each maps to: [model class, timestamp column, scope method (optional)].
     */
    private const SYNCABLE_ENTITIES = [
        'stores' => [Store::class, 'updated_at'],
        'products' => [Product::class, 'updated_at'],
        'route_plans' => [RoutePlan::class, 'updated_at'],
        'campaigns' => [Campaign::class, 'updated_at'],
        'notifications' => [Notification::class, 'updated_at'],
    ];

    /**
     * Pull changes since last sync for the given device.
     */
    public function pull(int $tenantId, int $userId, string $deviceId, ?string $since = null, array $entities = []): array
    {
        $sinceDate = $since ? Carbon::parse($since) : null;
        $now = now();

        // Get or create sync token
        $token = SyncToken::firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'device_id' => $deviceId],
            ['last_pulled_at' => null, 'last_pushed_at' => null, 'pending_count' => 0]
        );

        // If no since date provided, use the last_pulled_at from token
        $effectiveSince = $sinceDate ?? $token->last_pulled_at;

        // Determine which entities to sync
        $targetEntities = !empty($entities)
            ? array_intersect_key(self::SYNCABLE_ENTITIES, array_flip($entities))
            : self::SYNCABLE_ENTITIES;

        $changes = [];

        foreach ($targetEntities as $entityName => [$modelClass, $timestampCol]) {
            $query = $modelClass::where('tenant_id', $tenantId);

            if ($effectiveSince) {
                $query->where($timestampCol, '>', $effectiveSince);
            }

            // For notifications, scope to user
            if ($entityName === 'notifications') {
                $query->where('user_id', $userId);
            }

            // For route plans, scope to plans assigned to the user
            if ($entityName === 'route_plans') {
                $query->where('assigned_to', $userId);
            }

            $records = $query->orderBy($timestampCol)->limit(500)->get();

            $changes[$entityName] = [
                'count' => $records->count(),
                'data' => $records->toArray(),
            ];
        }

        // Update sync token
        $token->update(['last_pulled_at' => $now]);

        return [
            'synced_at' => $now->toIso8601String(),
            'since' => $effectiveSince?->toIso8601String(),
            'changes' => $changes,
        ];
    }

    /**
     * Push offline actions from the mobile device.
     */
    public function push(int $tenantId, int $userId, string $deviceId, array $actions): array
    {
        $results = [];

        DB::beginTransaction();

        try {
            foreach ($actions as $action) {
                $clientId = $action['client_id'] ?? null;

                // Idempotency check — skip if already processed
                if ($clientId) {
                    $existing = SyncQueueItem::where('client_id', $clientId)->first();
                    if ($existing) {
                        $results[] = [
                            'client_id' => $clientId,
                            'status' => $existing->status,
                            'message' => 'Already processed',
                            'server_response' => $existing->server_response,
                        ];
                        continue;
                    }
                }

                // Enqueue the sync action
                $item = SyncQueueItem::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'device_id' => $deviceId,
                    'client_id' => $clientId ?? uniqid('sync_', true),
                    'entity_type' => $action['entity_type'],
                    'action' => $action['action'],
                    'payload' => $action['payload'] ?? [],
                    'client_timestamp' => $action['timestamp'] ?? now(),
                    'status' => 'pending',
                ]);

                // Process immediately
                $result = $this->processQueueItem($item);
                $results[] = $result;
            }

            DB::commit();

            // Update sync token
            SyncToken::updateOrCreate(
                ['tenant_id' => $tenantId, 'user_id' => $userId, 'device_id' => $deviceId],
                ['last_pushed_at' => now(), 'pending_count' => 0]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sync push failed', [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return [
            'processed' => count($results),
            'results' => $results,
        ];
    }

    /**
     * Process a single sync queue item.
     */
    private function processQueueItem(SyncQueueItem $item): array
    {
        try {
            $result = match ($item->entity_type) {
                'gps_log' => $this->processGpsLog($item),
                'store_visit_checkin' => $this->processCheckIn($item),
                'store_visit_checkout' => $this->processCheckOut($item),
                'sales_order' => $this->processSalesOrder($item),
                'competitor_observation' => $this->processCompetitorObservation($item),
                default => ['status' => 'failed', 'message' => "Unknown entity type: {$item->entity_type}"],
            };

            $status = $result['status'] ?? 'processed';
            $item->update([
                'status' => $status,
                'server_response' => $result,
                'processed_at' => now(),
            ]);

            return [
                'client_id' => $item->client_id,
                'status' => $status,
                'server_response' => $result,
            ];

        } catch (\Exception $e) {
            $item->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processed_at' => now(),
            ]);

            return [
                'client_id' => $item->client_id,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function processGpsLog(SyncQueueItem $item): array
    {
        $payload = $item->payload;
        $routeService = app(RouteService::class);

        $routeService->logGps([
            'tenant_id' => $item->tenant_id,
            'user_id' => $item->user_id,
            'route_instance_id' => $payload['route_instance_id'] ?? null,
            'latitude' => $payload['latitude'],
            'longitude' => $payload['longitude'],
            'altitude' => $payload['altitude'] ?? null,
            'speed_kmh' => $payload['speed_kmh'] ?? null,
            'accuracy_meters' => $payload['accuracy_meters'] ?? null,
            'recorded_at' => $payload['recorded_at'] ?? $item->client_timestamp,
        ]);

        return ['status' => 'processed', 'message' => 'GPS log recorded'];
    }

    private function processCheckIn(SyncQueueItem $item): array
    {
        $payload = $item->payload;
        $routeService = app(RouteService::class);

        $routeService->checkIn($payload['store_visit_id'], [
            'latitude' => $payload['latitude'],
            'longitude' => $payload['longitude'],
            'qr_code' => $payload['qr_code'] ?? null,
        ]);

        return ['status' => 'processed', 'message' => 'Check-in recorded'];
    }

    private function processCheckOut(SyncQueueItem $item): array
    {
        $payload = $item->payload;
        $routeService = app(RouteService::class);

        $routeService->checkOut($payload['store_visit_id'], [
            'latitude' => $payload['latitude'] ?? null,
            'longitude' => $payload['longitude'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ]);

        return ['status' => 'processed', 'message' => 'Check-out recorded'];
    }

    private function processSalesOrder(SyncQueueItem $item): array
    {
        $payload = $item->payload;
        $salesService = app(SalesService::class);

        $order = $salesService->createOrder([
            'tenant_id' => $item->tenant_id,
            'user_id' => $item->user_id,
            'store_id' => $payload['store_id'],
            'items' => $payload['items'] ?? [],
            'notes' => $payload['notes'] ?? null,
        ]);

        return [
            'status' => 'processed',
            'message' => 'Sales order created',
            'server_id' => $order->id ?? null,
        ];
    }

    private function processCompetitorObservation(SyncQueueItem $item): array
    {
        $payload = $item->payload;
        $competitorService = app(CompetitorService::class);

        $observation = $competitorService->createObservation([
            'tenant_id' => $item->tenant_id,
            'user_id' => $item->user_id,
            'store_visit_id' => $payload['store_visit_id'] ?? null,
            'competitor_product_id' => $payload['competitor_product_id'],
            'type' => $payload['type'],
            'metrics' => $payload['metrics'] ?? [],
            'notes' => $payload['notes'] ?? null,
        ]);

        return [
            'status' => 'processed',
            'message' => 'Observation recorded',
            'server_id' => $observation->id ?? null,
        ];
    }

    /**
     * Get sync status for a device.
     */
    public function getStatus(int $tenantId, int $userId, string $deviceId): array
    {
        $token = SyncToken::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->first();

        $pendingCount = SyncQueueItem::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('status', 'pending')
            ->count();

        $failedCount = SyncQueueItem::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('status', 'failed')
            ->count();

        $conflictCount = SyncQueueItem::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('status', 'conflict')
            ->count();

        return [
            'last_pulled_at' => $token?->last_pulled_at?->toIso8601String(),
            'last_pushed_at' => $token?->last_pushed_at?->toIso8601String(),
            'pending_actions' => $pendingCount,
            'failed_actions' => $failedCount,
            'conflicts' => $conflictCount,
        ];
    }

    /**
     * Get failed/conflict sync items for resolution.
     */
    public function getConflicts(int $tenantId, int $userId, string $deviceId): array
    {
        return SyncQueueItem::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->whereIn('status', ['failed', 'conflict'])
            ->orderBy('client_timestamp')
            ->get()
            ->toArray();
    }

    /**
     * Retry failed sync items.
     */
    public function retryFailed(int $tenantId, int $userId, string $deviceId): array
    {
        $items = SyncQueueItem::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('status', 'failed')
            ->orderBy('client_timestamp')
            ->get();

        $results = [];
        foreach ($items as $item) {
            $results[] = $this->processQueueItem($item);
        }

        return ['retried' => count($results), 'results' => $results];
    }
}
