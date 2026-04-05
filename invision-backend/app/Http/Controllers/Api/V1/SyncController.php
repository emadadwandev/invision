<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(
        private readonly SyncService $syncService,
    ) {}

    /**
     * Pull changes since last sync.
     *
     * GET /api/v1/sync/pull?device_id=xxx&since=2026-01-01T00:00:00Z&entities[]=stores&entities[]=products
     */
    public function pull(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
            'since' => 'nullable|date',
            'entities' => 'nullable|array',
            'entities.*' => 'string|in:stores,products,route_plans,campaigns,notifications',
        ]);

        $result = $this->syncService->pull(
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            deviceId: $request->input('device_id'),
            since: $request->input('since'),
            entities: $request->input('entities', []),
        );

        return response()->json($result);
    }

    /**
     * Push offline actions to the server.
     *
     * POST /api/v1/sync/push
     * Body: { device_id: "xxx", actions: [{ client_id, entity_type, action, payload, timestamp }] }
     */
    public function push(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
            'actions' => 'required|array|max:100',
            'actions.*.client_id' => 'required|string|max:100',
            'actions.*.entity_type' => 'required|string|max:60',
            'actions.*.action' => 'required|string|in:create,update,delete',
            'actions.*.payload' => 'required|array',
            'actions.*.timestamp' => 'nullable|date',
        ]);

        $result = $this->syncService->push(
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            deviceId: $request->input('device_id'),
            actions: $request->input('actions'),
        );

        return response()->json($result);
    }

    /**
     * Get sync status for a device.
     *
     * GET /api/v1/sync/status?device_id=xxx
     */
    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
        ]);

        $result = $this->syncService->getStatus(
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            deviceId: $request->input('device_id'),
        );

        return response()->json($result);
    }

    /**
     * Get failed/conflict sync items.
     *
     * GET /api/v1/sync/conflicts?device_id=xxx
     */
    public function conflicts(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
        ]);

        $result = $this->syncService->getConflicts(
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            deviceId: $request->input('device_id'),
        );

        return response()->json(['conflicts' => $result]);
    }

    /**
     * Retry failed sync items.
     *
     * POST /api/v1/sync/retry?device_id=xxx
     */
    public function retry(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:100',
        ]);

        $result = $this->syncService->retryFailed(
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            deviceId: $request->input('device_id'),
        );

        return response()->json($result);
    }
}
