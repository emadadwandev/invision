<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CommandCenterService;
use Illuminate\Http\JsonResponse;

class CommandCenterController extends Controller
{
    public function __construct(
        private readonly CommandCenterService $service,
    ) {}

    /**
     * Dashboard stats (online count, active routes, today's sales).
     */
    public function stats(): JsonResponse
    {
        $stats = $this->service->getDashboardStats();

        return response()->json(['data' => $stats]);
    }

    /**
     * Get latest positions of all field force users.
     */
    public function fieldForcePositions(): JsonResponse
    {
        $positions = $this->service->getFieldForcePositions();

        return response()->json(['data' => $positions->values()]);
    }

    /**
     * Get all stores with summary data for map pins.
     */
    public function storeMapData(): JsonResponse
    {
        $stores = $this->service->getStoreMapData();

        return response()->json(['data' => $stores->values()]);
    }

    /**
     * Get detailed inquiry data for a specific store.
     */
    public function storeInquiry(int $storeId): JsonResponse
    {
        $data = $this->service->getStoreInquiry($storeId);

        return response()->json(['data' => $data]);
    }

    /**
     * Get a user's current activity and GPS trail for today.
     */
    public function userActivity(int $userId): JsonResponse
    {
        $data = $this->service->getUserActivity($userId);

        return response()->json(['data' => $data]);
    }
}
