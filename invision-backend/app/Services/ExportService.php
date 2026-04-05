<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ExportService
{
    /**
     * Export data to CSV format.
     */
    public function toCsv(array $headers, array $rows, string $filename): string
    {
        $path = "exports/{$filename}.csv";
        $handle = fopen('php://temp', 'r+');

        // BOM for Excel UTF-8 compatibility
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($path, $content);

        return $path;
    }

    /**
     * Export data to JSON format (for downstream use by PPT/Excel generators).
     */
    public function toJson(array $data, string $filename): string
    {
        $path = "exports/{$filename}.json";
        Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $path;
    }

    /**
     * Build an HTML-based report that can be rendered as PDF.
     */
    public function toHtmlReport(string $title, array $sections, array $options = []): string
    {
        $css = $options['css'] ?? $this->defaultCss();
        $logo = $options['logo'] ?? '';

        $html = "<!DOCTYPE html><html><head><meta charset='utf-8'><title>{$title}</title><style>{$css}</style></head><body>";
        $html .= "<div class='header'>";
        if ($logo) {
            $html .= "<img src='{$logo}' class='logo'/>";
        }
        $html .= "<h1>{$title}</h1>";
        $html .= "<p class='date'>Generated: " . now()->format('F j, Y h:i A') . "</p>";
        $html .= "</div>";

        foreach ($sections as $section) {
            $html .= "<div class='section'>";
            $html .= "<h2>{$section['title']}</h2>";

            if (!empty($section['summary'])) {
                $html .= "<p class='summary'>{$section['summary']}</p>";
            }

            if (!empty($section['table'])) {
                $html .= $this->buildTable($section['table']['headers'], $section['table']['rows']);
            }

            if (!empty($section['kpis'])) {
                $html .= $this->buildKpiGrid($section['kpis']);
            }

            if (!empty($section['html'])) {
                $html .= $section['html'];
            }

            $html .= "</div>";
        }

        $html .= "</body></html>";

        $filename = 'exports/report_' . now()->format('Ymd_His') . '.html';
        Storage::disk('local')->put($filename, $html);

        return $filename;
    }

    /**
     * Build a presentation data structure (slides) for PowerPoint-like export.
     */
    public function toPresentationData(string $title, array $slides): array
    {
        $presentation = [
            'title' => $title,
            'generated_at' => now()->toIso8601String(),
            'slides' => [],
        ];

        foreach ($slides as $slide) {
            $presentation['slides'][] = [
                'title' => $slide['title'] ?? '',
                'layout' => $slide['layout'] ?? 'content', // title, content, two_column, chart, table
                'content' => $slide['content'] ?? [],
                'notes' => $slide['notes'] ?? '',
            ];
        }

        $path = 'exports/presentation_' . now()->format('Ymd_His') . '.json';
        Storage::disk('local')->put($path, json_encode($presentation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ['path' => $path, 'data' => $presentation];
    }

    protected function buildTable(array $headers, array $rows): string
    {
        $html = "<table><thead><tr>";
        foreach ($headers as $h) {
            $html .= "<th>{$h}</th>";
        }
        $html .= "</tr></thead><tbody>";
        foreach ($rows as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>{$cell}</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        return $html;
    }

    protected function buildKpiGrid(array $kpis): string
    {
        $html = "<div class='kpi-grid'>";
        foreach ($kpis as $kpi) {
            $html .= "<div class='kpi-card'>";
            $html .= "<div class='kpi-value'>{$kpi['value']}</div>";
            $html .= "<div class='kpi-label'>{$kpi['label']}</div>";
            if (!empty($kpi['change'])) {
                $changeClass = $kpi['change'] >= 0 ? 'positive' : 'negative';
                $html .= "<div class='kpi-change {$changeClass}'>{$kpi['change']}%</div>";
            }
            $html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }

    protected function defaultCss(): string
    {
        return <<<'CSS'
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; color: #333; }
        .header { border-bottom: 3px solid #0E5A8A; padding-bottom: 12px; margin-bottom: 24px; }
        .header h1 { margin: 0; color: #0E5A8A; }
        .header .date { color: #666; font-size: 13px; }
        .logo { max-height: 40px; margin-bottom: 8px; }
        .section { margin-bottom: 28px; }
        .section h2 { color: #0E5A8A; border-bottom: 1px solid #ddd; padding-bottom: 6px; }
        .summary { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #0E5A8A; color: #fff; padding: 8px 12px; text-align: left; }
        td { padding: 6px 12px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) { background: #f9f9f9; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-top: 12px; }
        .kpi-card { background: #f5f8fa; border-radius: 8px; padding: 16px; text-align: center; }
        .kpi-value { font-size: 28px; font-weight: bold; color: #0E5A8A; }
        .kpi-label { font-size: 13px; color: #666; margin-top: 4px; }
        .kpi-change { font-size: 12px; margin-top: 4px; }
        .positive { color: #27ae60; }
        .negative { color: #e74c3c; }
        CSS;
    }
}
