<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    /**
     * Fixed reports index page.
     */
    public function index()
    {
        return view('pages.reports.index');
    }

    /**
     * Show a specific fixed report.
     */
    public function show(Request $request, string $type)
    {
        $filters = $request->only(['date_from', 'date_to', 'store_id', 'type']);

        $report = match ($type) {
            'sell-through'          => $this->reportService->sellThroughReport($filters),
            'sell-out'              => $this->reportService->sellOutReport($filters),
            'sell-in'               => $this->reportService->sellInReport($filters),
            'stock-movement'        => $this->reportService->stockMovementReport($filters),
            'vendor-ranking'        => $this->reportService->vendorRankingReport($filters),
            'sales-rep-performance' => $this->reportService->salesRepPerformanceReport($filters),
            default                 => abort(404),
        };

        return view('pages.reports.show', compact('report', 'type', 'filters'));
    }

    /**
     * Excel export for a specific report.
     */
    public function exportExcel(Request $request, string $type)
    {
        $filters = $request->only(['date_from', 'date_to', 'store_id', 'type']);

        $report = match ($type) {
            'sell-through'          => $this->reportService->sellThroughReport($filters),
            'sell-out'              => $this->reportService->sellOutReport($filters),
            'sell-in'               => $this->reportService->sellInReport($filters),
            'stock-movement'        => $this->reportService->stockMovementReport($filters),
            'vendor-ranking'        => $this->reportService->vendorRankingReport($filters),
            'sales-rep-performance' => $this->reportService->salesRepPerformanceReport($filters),
            default                 => abort(404),
        };

        return $this->reportService->exportExcel($report);
    }

    /**
     * PDF export for a specific report.
     */
    public function exportPdf(Request $request, string $type)
    {
        $filters = $request->only(['date_from', 'date_to', 'store_id', 'type']);

        $report = match ($type) {
            'sell-through'          => $this->reportService->sellThroughReport($filters),
            'sell-out'              => $this->reportService->sellOutReport($filters),
            'sell-in'               => $this->reportService->sellInReport($filters),
            'stock-movement'        => $this->reportService->stockMovementReport($filters),
            'vendor-ranking'        => $this->reportService->vendorRankingReport($filters),
            'sales-rep-performance' => $this->reportService->salesRepPerformanceReport($filters),
            default                 => abort(404),
        };

        return $this->reportService->exportPdf($report);
    }

    /**
     * Dynamic report builder page.
     */
    public function builder()
    {
        $entities = $this->reportService->reportEntities();

        return view('pages.reports.builder', compact('entities'));
    }

    /**
     * Run the dynamic report builder.
     */
    public function runBuilder(Request $request)
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

        $entities = $this->reportService->reportEntities();
        $report   = $this->reportService->buildDynamicReport($request->all());

        return view('pages.reports.builder', compact('entities', 'report'));
    }

    /**
     * Export dynamic report to Excel.
     */
    public function exportBuilderExcel(Request $request)
    {
        $report = $this->reportService->buildDynamicReport($request->all());

        return $this->reportService->exportExcel($report);
    }

    /**
     * Export dynamic report to PDF.
     */
    public function exportBuilderPdf(Request $request)
    {
        $report = $this->reportService->buildDynamicReport($request->all());

        return $this->reportService->exportPdf($report);
    }
}
