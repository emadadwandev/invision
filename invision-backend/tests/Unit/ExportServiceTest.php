<?php

namespace Tests\Unit;

use App\Services\ExportService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    protected ExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->service = new ExportService();
    }

    public function test_to_csv_creates_file_with_correct_content(): void
    {
        $headers = ['Name', 'Email', 'Score'];
        $rows = [
            ['Alice', 'alice@example.com', '95'],
            ['Bob', 'bob@example.com', '87'],
        ];

        $path = $this->service->toCsv($headers, $rows, 'test_export');

        $this->assertStringStartsWith('exports/', $path);
        $this->assertStringEndsWith('.csv', $path);
        Storage::disk('local')->assertExists($path);

        $content = Storage::disk('local')->get($path);
        $this->assertStringContainsString('Name,Email,Score', $content);
        $this->assertStringContainsString('Alice,alice@example.com,95', $content);
        $this->assertStringContainsString('Bob,bob@example.com,87', $content);
    }

    public function test_to_csv_handles_empty_rows(): void
    {
        $path = $this->service->toCsv(['Col1', 'Col2'], [], 'empty_export');

        Storage::disk('local')->assertExists($path);
        $content = Storage::disk('local')->get($path);
        $this->assertStringContainsString('Col1,Col2', $content);
    }

    public function test_to_json_creates_valid_json_file(): void
    {
        $data = ['key' => 'value', 'items' => [1, 2, 3]];

        $path = $this->service->toJson($data, 'test_json');

        $this->assertStringEndsWith('.json', $path);
        Storage::disk('local')->assertExists($path);

        $content = json_decode(Storage::disk('local')->get($path), true);
        $this->assertEquals('value', $content['key']);
        $this->assertCount(3, $content['items']);
    }

    public function test_to_html_report_generates_valid_html(): void
    {
        $sections = [
            [
                'title' => 'Summary',
                'summary' => 'This is a test summary.',
            ],
            [
                'title' => 'Data Table',
                'table' => [
                    'headers' => ['Product', 'Sales'],
                    'rows' => [['Widget A', '100'], ['Widget B', '200']],
                ],
            ],
            [
                'title' => 'KPIs',
                'kpis' => [
                    ['label' => 'Total', 'value' => '300'],
                    ['label' => 'Growth', 'value' => '15%', 'change' => 15],
                ],
            ],
        ];

        $path = $this->service->toHtmlReport('Test Report', $sections);

        Storage::disk('local')->assertExists($path);
        $html = Storage::disk('local')->get($path);

        $this->assertStringContainsString('<h1>Test Report</h1>', $html);
        $this->assertStringContainsString('This is a test summary.', $html);
        $this->assertStringContainsString('<th>Product</th>', $html);
        $this->assertStringContainsString('Widget A', $html);
        $this->assertStringContainsString('kpi-card', $html);
        $this->assertStringContainsString('300', $html);
        $this->assertStringContainsString('positive', $html); // positive change
    }

    public function test_to_presentation_data_creates_slide_structure(): void
    {
        $slides = [
            ['title' => 'Title Slide', 'layout' => 'title', 'content' => ['subtitle' => 'Hello']],
            ['title' => 'KPIs', 'layout' => 'kpi_grid', 'content' => [['label' => 'Revenue', 'value' => '1000']]],
        ];

        $result = $this->service->toPresentationData('My Presentation', $slides);

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('My Presentation', $result['data']['title']);
        $this->assertCount(2, $result['data']['slides']);
        $this->assertEquals('Title Slide', $result['data']['slides'][0]['title']);
        $this->assertEquals('title', $result['data']['slides'][0]['layout']);

        Storage::disk('local')->assertExists($result['path']);
    }

    public function test_to_html_report_with_custom_css(): void
    {
        $sections = [['title' => 'Test', 'summary' => 'Content']];
        $options = ['css' => 'body { color: red; }'];

        $path = $this->service->toHtmlReport('Custom CSS', $sections, $options);
        $html = Storage::disk('local')->get($path);

        $this->assertStringContainsString('body { color: red; }', $html);
    }

    public function test_kpi_with_negative_change_shows_negative_class(): void
    {
        $sections = [
            [
                'title' => 'KPIs',
                'kpis' => [
                    ['label' => 'Decline', 'value' => '50', 'change' => -10],
                ],
            ],
        ];

        $path = $this->service->toHtmlReport('Negative KPI', $sections);
        $html = Storage::disk('local')->get($path);

        $this->assertStringContainsString('negative', $html);
        $this->assertStringContainsString('-10%', $html);
    }
}
