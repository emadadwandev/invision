<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    // ─── Fixed Reports ────────────────────────────────────────────────

    public function sellThrough(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->reportService->sellThroughReport($request->all())]);
    }

    public function sellOut(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->reportService->sellOutReport($request->all())]);
    }

    public function sellIn(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->reportService->sellInReport($request->all())]);
    }

    public function stockMovement(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->reportService->stockMovementReport($request->all())]);
    }

    public function vendorRanking(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->reportService->vendorRankingReport($request->all())]);
    }

    public function salesRepPerformance(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->reportService->salesRepPerformanceReport($request->all())]);
    }

    // ─── Dynamic Report ───────────────────────────────────────────────

    public function entities(): JsonResponse
    {
        return response()->json(['data' => $this->reportService->reportEntities()]);
    }

    public function buildReport(Request $request): JsonResponse
    {
        $request->validate([
            'entity'   => 'required|string|in:sales_orders,stores,products,route_instances,pos_transactions',
            'columns'  => 'nullable|array',
            'filters'  => 'nullable|array',
            'group_by' => 'nullable|string',
            'order_by' => 'nullable|string',
            'order_dir'=> 'nullable|string|in:asc,desc',
            'limit'    => 'nullable|integer|min:1|max:5000',
        ]);

        return response()->json(['data' => $this->reportService->buildDynamicReport($request->all())]);
    }

    // ─── Exports ──────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $report = $this->resolveReport($request);

        return $this->reportService->exportExcel($report);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->resolveReport($request);

        return $this->reportService->exportPdf($report);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function resolveReport(Request $request): array
    {
        $type = $request->input('report_type', 'sell_through');

        return match ($type) {
            'sell_through'         => $this->reportService->sellThroughReport($request->all()),
            'sell_out'             => $this->reportService->sellOutReport($request->all()),
            'sell_in'              => $this->reportService->sellInReport($request->all()),
            'stock_movement'       => $this->reportService->stockMovementReport($request->all()),
            'vendor_ranking'       => $this->reportService->vendorRankingReport($request->all()),
            'sales_rep_performance'=> $this->reportService->salesRepPerformanceReport($request->all()),
            'custom'               => $this->reportService->buildDynamicReport($request->all()),
            default                => $this->reportService->sellThroughReport($request->all()),
        };
    }
}
