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

        return view('pages.command-center.index', compact('stats', 'fieldForce', 'stores'));
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
