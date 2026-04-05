<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PresentationTemplate;
use App\Models\ReportTemplate;
use App\Models\SavedExport;
use App\Services\ExportService;
use App\Services\PresentationService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresentationController extends Controller
{
    public function __construct(
        protected PresentationService $presentationService,
        protected ExportService $exportService,
        protected ReportService $reportService,
    ) {}

    // ─── Report Templates ─────────────────────────────────────────────

    public function reportTemplates(Request $request): JsonResponse
    {
        $templates = ReportTemplate::where('created_by', $request->user()->id)
            ->orWhere('is_shared', true)
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['data' => $templates]);
    }

    public function storeReportTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:overview,sales,field_activity,custom',
            'description' => 'nullable|string',
            'config' => 'required|array',
            'layout' => 'nullable|array',
            'is_shared' => 'boolean',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['tenant_id'] = $request->user()->tenant_id;

        $template = ReportTemplate::create($validated);

        return response()->json(['data' => $template], 201);
    }

    public function showReportTemplate(ReportTemplate $reportTemplate): JsonResponse
    {
        return response()->json(['data' => $reportTemplate]);
    }

    public function updateReportTemplate(Request $request, ReportTemplate $reportTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'config' => 'array',
            'layout' => 'nullable|array',
            'is_shared' => 'boolean',
            'is_favorite' => 'boolean',
        ]);

        $reportTemplate->update($validated);

        return response()->json(['data' => $reportTemplate]);
    }

    public function destroyReportTemplate(ReportTemplate $reportTemplate): JsonResponse
    {
        $reportTemplate->delete();

        return response()->json(['message' => 'Template deleted']);
    }

    // ─── Generate Report from Template ────────────────────────────────

    public function generateFromTemplate(Request $request, ReportTemplate $reportTemplate): JsonResponse
    {
        $config = array_merge($reportTemplate->config, $request->only(['date_from', 'date_to']));

        if ($reportTemplate->type === 'custom') {
            $report = $this->reportService->buildDynamicReport($config);
        } else {
            // Use the template type to pick the generator
            $report = [
                'title' => $reportTemplate->name,
                'type' => $reportTemplate->type,
                'generated' => now()->toDateTimeString(),
                'data' => $config,
            ];
        }

        $reportTemplate->update(['last_generated_at' => now()]);

        return response()->json(['data' => $report]);
    }

    // ─── Export from Template ─────────────────────────────────────────

    public function exportTemplateExcel(Request $request, ReportTemplate $reportTemplate)
    {
        $config = array_merge($reportTemplate->config, $request->only(['date_from', 'date_to']));
        $report = $this->reportService->buildDynamicReport($config);

        return $this->reportService->exportExcel($report);
    }

    public function exportTemplatePdf(Request $request, ReportTemplate $reportTemplate)
    {
        $config = array_merge($reportTemplate->config, $request->only(['date_from', 'date_to']));
        $report = $this->reportService->buildDynamicReport($config);

        return $this->reportService->exportPdf($report);
    }

    public function exportTemplateCsv(Request $request, ReportTemplate $reportTemplate): JsonResponse
    {
        $config = array_merge($reportTemplate->config, $request->only(['date_from', 'date_to']));
        $report = $this->reportService->buildDynamicReport($config);

        $rows = $report['rows'] ?? [];
        $headers = !empty($rows) ? array_keys($rows[0]) : [];
        $csvRows = array_map(fn($row) => array_values($row), $rows);

        $path = $this->exportService->toCsv($headers, $csvRows, $reportTemplate->name . '_' . now()->format('Ymd_His'));

        $this->presentationService->saveExportRecord(
            $request->user()->id,
            $reportTemplate->name,
            'csv',
            $path,
            $request->user()->tenant_id,
            $reportTemplate->id,
        );

        return response()->json(['data' => ['path' => $path, 'message' => 'CSV exported successfully']]);
    }

    // ─── Presentation Templates ───────────────────────────────────────

    public function presentationTemplates(Request $request): JsonResponse
    {
        $templates = PresentationTemplate::where('tenant_id', $request->user()->tenant_id)
            ->orWhereNull('tenant_id')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $templates]);
    }

    public function storePresentationTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:general,market_review,sales_review,field_report',
            'description' => 'nullable|string',
            'slide_definitions' => 'required|array',
            'theme' => 'nullable|array',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $template = PresentationTemplate::create($validated);

        return response()->json(['data' => $template], 201);
    }

    public function generatePresentation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:market_review,template',
            'template_id' => 'required_if:type,template|exists:presentation_templates,id',
            'period' => 'nullable|string|in:week,month,quarter,year',
        ]);

        if ($validated['type'] === 'market_review') {
            $result = $this->presentationService->generateMarketReview($validated);
        } else {
            $template = PresentationTemplate::findOrFail($validated['template_id']);
            $result = $this->presentationService->generateFromTemplate($template, $validated);
        }

        $this->presentationService->saveExportRecord(
            $request->user()->id,
            $result['data']['title'] ?? 'Presentation',
            'presentation',
            $result['path'],
            $request->user()->tenant_id,
        );

        return response()->json(['data' => $result['data']]);
    }

    public function presentationToHtml(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:market_review,template',
            'template_id' => 'required_if:type,template|exists:presentation_templates,id',
            'period' => 'nullable|string|in:week,month,quarter,year',
        ]);

        if ($validated['type'] === 'market_review') {
            $presentationData = $this->presentationService->generateMarketReview($validated);
        } else {
            $template = PresentationTemplate::findOrFail($validated['template_id']);
            $presentationData = $this->presentationService->generateFromTemplate($template, $validated);
        }

        $htmlPath = $this->presentationService->toHtmlFromPresentation($presentationData);

        $this->presentationService->saveExportRecord(
            $request->user()->id,
            $presentationData['data']['title'] ?? 'Presentation',
            'html',
            $htmlPath,
            $request->user()->tenant_id,
        );

        return response()->json([
            'data' => [
                'path' => $htmlPath,
                'message' => 'HTML report generated',
            ],
        ]);
    }

    // ─── Saved Exports ────────────────────────────────────────────────

    public function savedExports(Request $request): JsonResponse
    {
        $exports = SavedExport::where('user_id', $request->user()->id)
            ->with('template:id,name')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($exports);
    }

    public function destroySavedExport(SavedExport $savedExport): JsonResponse
    {
        $savedExport->delete();

        return response()->json(['message' => 'Export record deleted']);
    }
}
