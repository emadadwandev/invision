<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
    ) {}

    /**
     * Overview KPI cards.
     */
    public function overview(): JsonResponse
    {
        return response()->json(['data' => $this->service->getOverviewKpis()]);
    }

    /**
     * Sales performance KPIs.
     */
    public function sales(Request $request): JsonResponse
    {
        $period = $request->query('period', 'month');
        return response()->json(['data' => $this->service->getSalesKpis($period)]);
    }

    /**
     * Route & visit KPIs.
     */
    public function routes(Request $request): JsonResponse
    {
        $period = $request->query('period', 'month');
        return response()->json(['data' => $this->service->getRouteKpis($period)]);
    }

    /**
     * Campaign performance KPIs.
     */
    public function campaigns(): JsonResponse
    {
        return response()->json(['data' => $this->service->getCampaignKpis()]);
    }

    /**
     * POS performance KPIs.
     */
    public function pos(Request $request): JsonResponse
    {
        $period = $request->query('period', 'month');
        return response()->json(['data' => $this->service->getPosKpis($period)]);
    }

    /**
     * Credit & collections KPIs.
     */
    public function credits(): JsonResponse
    {
        return response()->json(['data' => $this->service->getCreditKpis()]);
    }

    /**
     * Store inquiry with filters.
     */
    public function storeInquiry(Request $request): JsonResponse
    {
        $data = $this->service->getStoreInquiry($request->only([
            'search', 'category', 'rank', 'area_id',
        ]));

        return response()->json(['data' => $data->values()]);
    }

    /**
     * Sales inquiry with filters.
     */
    public function salesInquiry(Request $request): JsonResponse
    {
        $data = $this->service->getSalesInquiry($request->only([
            'search', 'status', 'store_id', 'user_id', 'date_from', 'date_to',
        ]));

        return response()->json(['data' => $data->values()]);
    }

    /**
     * Route inquiry with filters.
     */
    public function routeInquiry(Request $request): JsonResponse
    {
        $data = $this->service->getRouteInquiry($request->only([
            'status', 'user_id', 'date_from', 'date_to',
        ]));

        return response()->json(['data' => $data->values()]);
    }
}
