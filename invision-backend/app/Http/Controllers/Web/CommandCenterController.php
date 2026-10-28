<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CommandCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CommandCenterController extends Controller
{
    public function __construct(
        private readonly CommandCenterService $service,
    ) {}

    /**
     * Main command center dashboard with live map.
     */
    public function index(): View
    {
        $stats = $this->service->getDashboardStats();
        $fieldForce = $this->service->getFieldForcePositions();
        $stores = $this->service->getStoreMapData();
        $activity = $this->service->getRecentActivity();

        return view('pages.command-center.index', compact('stats', 'fieldForce', 'stores', 'activity'));
    }

    /**
     * Today's activity feed JSON for AJAX refresh.
     */
    public function activityJson(): JsonResponse
    {
        return response()->json(['data' => $this->service->getRecentActivity()->values()]);
    }

    /**
     * Field force positions JSON for AJAX refresh.
     */
    public function fieldForcePositionsJson(): JsonResponse
    {
        $positions = $this->service->getFieldForcePositions();

        return response()->json(['data' => $positions->values()]);
    }

    /**
     * Store inquiry modal data (AJAX).
     */
    public function storeInquiry(int $storeId): JsonResponse
    {
        $data = $this->service->getStoreInquiry($storeId);

        return response()->json(['data' => $data]);
    }

    /**
     * User activity data (AJAX).
     */
    public function userActivity(int $userId): JsonResponse
    {
        $data = $this->service->getUserActivity($userId);

        return response()->json(['data' => $data]);
    }
}
