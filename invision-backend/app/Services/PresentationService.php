<?php

namespace App\Services;

use App\Models\PresentationTemplate;
use App\Models\SavedExport;
use Illuminate\Support\Facades\Storage;

class PresentationService
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected ExportService $exportService,
    ) {}

    /**
     * Generate a presentation from a template and live data.
     */
    public function generateFromTemplate(PresentationTemplate $template, array $params = []): array
    {
        $slides = [];

        foreach ($template->slide_definitions as $slideDef) {
            $slides[] = $this->buildSlide($slideDef, $params);
        }

        return $this->exportService->toPresentationData($template->name, $slides);
    }

    /**
     * Generate a market review presentation.
     */
    public function generateMarketReview(array $params = []): array
    {
        $period = $params['period'] ?? 'month';
        $kpis = $this->dashboardService->getOverviewKpis();
        $salesKpis = $this->dashboardService->getSalesKpis($period);
        $routeKpis = $this->dashboardService->getRouteKpis($period);

        $slides = [
            [
                'title' => 'Market Review',
                'layout' => 'title',
                'content' => [
                    'subtitle' => 'Period: ' . ucfirst($period),
                    'date' => now()->format('F j, Y'),
                ],
                'notes' => 'Auto-generated market review presentation',
            ],
            [
                'title' => 'Key Performance Indicators',
                'layout' => 'kpi_grid',
                'content' => [
                    ['label' => 'Total Stores', 'value' => $kpis['total_stores']],
                    ['label' => 'Active Users', 'value' => $kpis['field_force_count']],
                    ['label' => 'Today Visits', 'value' => $kpis['today_visits']],
                    ['label' => 'Today Sales', 'value' => number_format($kpis['today_sales'], 2)],
                    ['label' => 'Active Campaigns', 'value' => $kpis['active_campaigns']],
                    ['label' => 'Active Routes', 'value' => $kpis['active_routes']],
                ],
                'notes' => 'Overview of current business metrics',
            ],
            [
                'title' => 'Sales Performance',
                'layout' => 'two_column',
                'content' => [
                    'left' => [
                        'title' => 'Revenue Summary',
                        'items' => [
                            'Total Revenue: ' . number_format($salesKpis['total_revenue'], 2),
                            'Total Orders: ' . $salesKpis['total_orders'],
                            'Avg Order Value: ' . number_format($salesKpis['avg_order_value'], 2),
                            'Delivered: ' . $salesKpis['delivered_count'],
                            'Cancelled: ' . $salesKpis['cancelled_count'],
                        ],
                    ],
                    'right' => [
                        'title' => 'Top Stores by Sales',
                        'items' => collect($salesKpis['top_stores'])->take(5)->map(
                            fn($s) => ($s['store_name'] ?? 'N/A') . ': ' . number_format($s['total_sales'], 2)
                        )->toArray(),
                    ],
                ],
                'notes' => 'Sales summary for the review period',
            ],
            [
                'title' => 'Top Sales Representatives',
                'layout' => 'table',
                'content' => [
                    'headers' => ['Name', 'Orders', 'Total Sales'],
                    'rows' => collect($salesKpis['top_sales_reps'])->take(10)->map(fn($r) => [
                        $r['name'] ?? 'N/A',
                        $r['order_count'],
                        number_format($r['total_sales'], 2),
                    ])->toArray(),
                ],
                'notes' => 'Top performing sales reps',
            ],
            [
                'title' => 'Field Activity Summary',
                'layout' => 'kpi_grid',
                'content' => [
                    ['label' => 'Total Visits', 'value' => $routeKpis['total_visits']],
                    ['label' => 'Completed', 'value' => $routeKpis['completed_visits']],
                    ['label' => 'Completion Rate', 'value' => $routeKpis['visit_completion_rate'] . '%'],
                    ['label' => 'Avg Duration', 'value' => $routeKpis['avg_visit_duration'] . ' min'],
                    ['label' => 'Routes Completed', 'value' => $routeKpis['completed_instances']],
                    ['label' => 'Route Completion', 'value' => $routeKpis['completion_rate'] . '%'],
                ],
                'notes' => 'Field activity and route statistics',
            ],
            [
                'title' => 'Thank You',
                'layout' => 'title',
                'content' => [
                    'subtitle' => 'Questions & Discussion',
                    'date' => now()->format('F j, Y'),
                ],
                'notes' => '',
            ],
        ];

        return $this->exportService->toPresentationData('Market Review - ' . ucfirst($period), $slides);
    }

    /**
     * Generate an HTML report from presentation data, suitable for PDF.
     */
    public function toHtmlFromPresentation(array $presentationData): string
    {
        $sections = [];

        foreach ($presentationData['data']['slides'] ?? [] as $slide) {
            $section = ['title' => $slide['title']];

            if ($slide['layout'] === 'kpi_grid' && is_array($slide['content'])) {
                $section['kpis'] = array_map(fn($item) => [
                    'label' => $item['label'] ?? '',
                    'value' => $item['value'] ?? '',
                ], $slide['content']);
            } elseif ($slide['layout'] === 'table' && isset($slide['content']['headers'])) {
                $section['table'] = [
                    'headers' => $slide['content']['headers'],
                    'rows' => $slide['content']['rows'] ?? [],
                ];
            } elseif ($slide['layout'] === 'two_column') {
                $html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">';
                foreach (['left', 'right'] as $side) {
                    $col = $slide['content'][$side] ?? [];
                    $html .= '<div>';
                    $html .= '<h3>' . ($col['title'] ?? '') . '</h3><ul>';
                    foreach ($col['items'] ?? [] as $item) {
                        $html .= '<li>' . e($item) . '</li>';
                    }
                    $html .= '</ul></div>';
                }
                $html .= '</div>';
                $section['html'] = $html;
            } elseif ($slide['layout'] === 'title') {
                $section['summary'] = ($slide['content']['subtitle'] ?? '') . ' — ' . ($slide['content']['date'] ?? '');
            }

            $sections[] = $section;
        }

        return $this->exportService->toHtmlReport(
            $presentationData['data']['title'] ?? 'Presentation',
            $sections
        );
    }

    /**
     * Save an export record.
     */
    public function saveExportRecord(int $userId, string $title, string $format, string $filePath, ?int $tenantId = null, ?int $templateId = null, ?array $params = null): SavedExport
    {
        $fileSize = Storage::disk('local')->exists($filePath) ? Storage::disk('local')->size($filePath) : 0;

        return SavedExport::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'report_template_id' => $templateId,
            'title' => $title,
            'format' => $format,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'parameters' => $params,
        ]);
    }

    protected function buildSlide(array $def, array $params): array
    {
        $dataSource = $def['data_source'] ?? null;
        $content = $def['content'] ?? [];

        if ($dataSource) {
            $content = $this->resolveDataSource($dataSource, $params);
        }

        return [
            'title' => $def['title'] ?? '',
            'layout' => $def['layout'] ?? 'content',
            'content' => $content,
            'notes' => $def['notes'] ?? '',
        ];
    }

    protected function resolveDataSource(string $source, array $params): array
    {
        $period = $params['period'] ?? 'month';

        return match ($source) {
            'overview_kpis' => $this->formatKpisForSlide($this->dashboardService->getOverviewKpis()),
            'sales_kpis' => $this->formatSalesKpisForSlide($this->dashboardService->getSalesKpis($period)),
            'route_kpis' => $this->formatRouteKpisForSlide($this->dashboardService->getRouteKpis($period)),
            'campaign_kpis' => $this->dashboardService->getCampaignKpis(),
            default => [],
        };
    }

    protected function formatKpisForSlide(array $kpis): array
    {
        return [
            ['label' => 'Total Stores', 'value' => $kpis['total_stores']],
            ['label' => 'Field Force', 'value' => $kpis['field_force_count']],
            ['label' => 'Online Now', 'value' => $kpis['online_now']],
            ['label' => 'Today Visits', 'value' => $kpis['today_visits']],
            ['label' => 'Today Sales', 'value' => number_format($kpis['today_sales'], 2)],
            ['label' => 'Active Campaigns', 'value' => $kpis['active_campaigns']],
        ];
    }

    protected function formatSalesKpisForSlide(array $kpis): array
    {
        return [
            ['label' => 'Revenue', 'value' => number_format($kpis['total_revenue'], 2)],
            ['label' => 'Orders', 'value' => $kpis['total_orders']],
            ['label' => 'Avg Order', 'value' => number_format($kpis['avg_order_value'], 2)],
            ['label' => 'Delivered', 'value' => $kpis['delivered_count']],
        ];
    }

    protected function formatRouteKpisForSlide(array $kpis): array
    {
        return [
            ['label' => 'Visits', 'value' => $kpis['total_visits']],
            ['label' => 'Completed', 'value' => $kpis['completed_visits']],
            ['label' => 'Completion', 'value' => $kpis['visit_completion_rate'] . '%'],
            ['label' => 'Avg Duration', 'value' => $kpis['avg_visit_duration'] . ' min'],
        ];
    }
}
