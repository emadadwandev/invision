<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PosTransactionType;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use App\Models\RouteInstance;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreInventory;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    // ─── Fixed Reports ────────────────────────────────────────────────

    /**
     * Weekly sell-through report (POS sell-through transactions grouped by product).
     */
    public function sellThroughReport(array $filters = []): array
    {
        $start = Carbon::parse($filters['date_from'] ?? now()->startOfWeek());
        $end   = Carbon::parse($filters['date_to'] ?? now()->endOfWeek());

        $rows = PosTransactionItem::query()
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->where('type', PosTransactionType::SellThrough->value)
                  ->where('status', 'completed')
                  ->whereBetween('created_at', [$start, $end]);
            })
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_amount'),
            )
            ->groupBy('product_id')
            ->with('product:id,name,sku,barcode')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($r) => [
                'product_id'   => $r->product_id,
                'product_name' => $r->product?->name,
                'sku'          => $r->product?->sku,
                'barcode'      => $r->product?->barcode,
                'total_qty'    => (int) $r->total_qty,
                'total_amount' => (float) $r->total_amount,
            ])->toArray();

        return [
            'title'     => 'Sell-Through Report',
            'period'    => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
            'generated' => now()->toDateTimeString(),
            'rows'      => $rows,
        ];
    }

    /**
     * Weekly sell-out report (POS sell-out transactions by product).
     */
    public function sellOutReport(array $filters = []): array
    {
        $start = Carbon::parse($filters['date_from'] ?? now()->startOfWeek());
        $end   = Carbon::parse($filters['date_to'] ?? now()->endOfWeek());

        $rows = PosTransactionItem::query()
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->where('type', PosTransactionType::SellOut->value)
                  ->where('status', 'completed')
                  ->whereBetween('created_at', [$start, $end]);
            })
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_amount'),
            )
            ->groupBy('product_id')
            ->with('product:id,name,sku,barcode')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($r) => [
                'product_id'   => $r->product_id,
                'product_name' => $r->product?->name,
                'sku'          => $r->product?->sku,
                'barcode'      => $r->product?->barcode,
                'total_qty'    => (int) $r->total_qty,
                'total_amount' => (float) $r->total_amount,
            ])->toArray();

        return [
            'title'     => 'Sell-Out Report',
            'period'    => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
            'generated' => now()->toDateTimeString(),
            'rows'      => $rows,
        ];
    }

    /**
     * Sell-in report (delivered sales orders grouped by product).
     */
    public function sellInReport(array $filters = []): array
    {
        $start = Carbon::parse($filters['date_from'] ?? now()->startOfWeek());
        $end   = Carbon::parse($filters['date_to'] ?? now()->endOfWeek());

        $rows = SalesOrderItem::query()
            ->whereHas('salesOrder', function ($q) use ($start, $end) {
                $q->where('status', OrderStatus::Delivered->value)
                  ->whereBetween('created_at', [$start, $end]);
            })
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_amount'),
            )
            ->groupBy('product_id')
            ->with('product:id,name,sku,barcode')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($r) => [
                'product_id'   => $r->product_id,
                'product_name' => $r->product?->name,
                'sku'          => $r->product?->sku,
                'barcode'      => $r->product?->barcode,
                'total_qty'    => (int) $r->total_qty,
                'total_amount' => (float) $r->total_amount,
            ])->toArray();

        return [
            'title'     => 'Sell-In Report',
            'period'    => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
            'generated' => now()->toDateTimeString(),
            'rows'      => $rows,
        ];
    }

    /**
     * Stock movement report.
     */
    public function stockMovementReport(array $filters = []): array
    {
        $start = Carbon::parse($filters['date_from'] ?? now()->startOfWeek());
        $end   = Carbon::parse($filters['date_to'] ?? now()->endOfWeek());

        $query = StockMovement::query()
            ->whereBetween('created_at', [$start, $end])
            ->with(['product:id,name,sku', 'store:id,name,code', 'user:id,first_name,last_name'])
            ->orderByDesc('created_at');

        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $rows = $query->get()->map(fn ($m) => [
            'id'           => $m->id,
            'date'         => $m->created_at->format('Y-m-d H:i'),
            'store'        => $m->store?->name,
            'product'      => $m->product?->name,
            'sku'          => $m->product?->sku,
            'type'         => $m->type,
            'quantity'     => $m->quantity,
            'direction'    => $m->direction,
            'reference'    => $m->reference_type,
            'user'         => $m->user ? ($m->user->first_name . ' ' . $m->user->last_name) : null,
            'notes'        => $m->notes,
        ])->toArray();

        return [
            'title'     => 'Stock Movement Report',
            'period'    => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
            'generated' => now()->toDateTimeString(),
            'rows'      => $rows,
        ];
    }

    /**
     * Vendor / Store ranking report.
     */
    public function vendorRankingReport(array $filters = []): array
    {
        $start = Carbon::parse($filters['date_from'] ?? now()->startOfMonth());
        $end   = Carbon::parse($filters['date_to'] ?? now()->endOfMonth());

        $rows = SalesOrder::query()
            ->where('status', OrderStatus::Delivered->value)
            ->whereBetween('created_at', [$start, $end])
            ->select(
                'store_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_order'),
            )
            ->groupBy('store_id')
            ->with('store:id,name,code,category,rank')
            ->orderByDesc('total_sales')
            ->get()
            ->map(fn ($r) => [
                'store_id'    => $r->store_id,
                'store_name'  => $r->store?->name,
                'store_code'  => $r->store?->code,
                'category'    => $r->store?->category,
                'rank'        => $r->store?->rank,
                'order_count' => (int) $r->order_count,
                'total_sales' => (float) $r->total_sales,
                'avg_order'   => round((float) $r->avg_order, 2),
            ])->toArray();

        return [
            'title'     => 'Vendor Ranking Report',
            'period'    => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
            'generated' => now()->toDateTimeString(),
            'rows'      => $rows,
        ];
    }

    /**
     * Sales rep performance report.
     */
    public function salesRepPerformanceReport(array $filters = []): array
    {
        $start = Carbon::parse($filters['date_from'] ?? now()->startOfMonth());
        $end   = Carbon::parse($filters['date_to'] ?? now()->endOfMonth());

        $rows = SalesOrder::query()
            ->where('status', OrderStatus::Delivered->value)
            ->whereBetween('created_at', [$start, $end])
            ->select(
                'user_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_order'),
                DB::raw('COUNT(DISTINCT store_id) as stores_visited'),
            )
            ->groupBy('user_id')
            ->with('salesperson:id,first_name,last_name,email,role')
            ->orderByDesc('total_sales')
            ->get()
            ->map(fn ($r) => [
                'user_id'        => $r->user_id,
                'name'           => $r->salesperson ? ($r->salesperson->first_name . ' ' . $r->salesperson->last_name) : null,
                'email'          => $r->salesperson?->email,
                'role'           => $r->salesperson?->role,
                'order_count'    => (int) $r->order_count,
                'total_sales'    => (float) $r->total_sales,
                'avg_order'      => round((float) $r->avg_order, 2),
                'stores_visited' => (int) $r->stores_visited,
            ])->toArray();

        // Add route completion for each rep
        foreach ($rows as &$row) {
            $routeStats = RouteInstance::query()
                ->where('user_id', $row['user_id'])
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    DB::raw('COUNT(*) as total_routes'),
                    DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_routes"),
                )
                ->first();

            $row['total_routes']     = (int) ($routeStats->total_routes ?? 0);
            $row['completed_routes'] = (int) ($routeStats->completed_routes ?? 0);
            $row['route_completion'] = $row['total_routes'] > 0
                ? round(($row['completed_routes'] / $row['total_routes']) * 100, 1)
                : 0;
        }

        return [
            'title'     => 'Sales Rep Performance Report',
            'period'    => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
            'generated' => now()->toDateTimeString(),
            'rows'      => $rows,
        ];
    }

    // ─── Dynamic Report Builder ───────────────────────────────────────

    /**
     * Build a dynamic report from user-defined params.
     *
     * $config = [
     *   'entity'  => 'sales_orders|stores|products|routes|pos_transactions',
     *   'columns' => ['store_name', 'total_amount', ...],
     *   'filters' => ['status' => 'delivered', 'date_from' => '...', 'date_to' => '...'],
     *   'group_by'=> 'store_id',   // optional
     *   'order_by'=> 'total_amount',
     *   'order_dir'=> 'desc',
     *   'limit'   => 100,
     * ]
     */
    public function buildDynamicReport(array $config): array
    {
        $entity = $config['entity'] ?? 'sales_orders';
        $query  = $this->resolveEntity($entity);

        // Apply date filter
        if (!empty($config['filters']['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($config['filters']['date_from']));
        }
        if (!empty($config['filters']['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($config['filters']['date_to'])->endOfDay());
        }

        // Apply status filter
        if (!empty($config['filters']['status'])) {
            $query->where('status', $config['filters']['status']);
        }

        // Apply store filter
        if (!empty($config['filters']['store_id'])) {
            $query->where('store_id', $config['filters']['store_id']);
        }

        // Apply user filter
        if (!empty($config['filters']['user_id'])) {
            $query->where('user_id', $config['filters']['user_id']);
        }

        // Group by
        if (!empty($config['group_by'])) {
            $groupCol = $config['group_by'];
            $aggColumns = $this->resolveAggregations($entity, $config['columns'] ?? []);
            $query->select(array_merge([$groupCol], $aggColumns))->groupBy($groupCol);
        }

        // Order
        $orderBy  = $config['order_by'] ?? 'created_at';
        $orderDir = $config['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        // Limit
        $limit = min((int) ($config['limit'] ?? 100), 5000);
        $query->limit($limit);

        // Load common relations
        $this->loadRelations($query, $entity);

        $rows = $query->get()->toArray();

        return [
            'title'     => 'Custom Report — ' . str_replace('_', ' ', ucfirst($entity)),
            'entity'    => $entity,
            'generated' => now()->toDateTimeString(),
            'count'     => count($rows),
            'rows'      => $rows,
        ];
    }

    /**
     * Available entities and their columns for the dynamic report builder.
     */
    public function reportEntities(): array
    {
        return [
            'sales_orders' => [
                'label'   => 'Sales Orders',
                'columns' => ['id', 'order_number', 'store_id', 'user_id', 'status', 'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'notes', 'created_at'],
                'group_by_options' => ['store_id', 'user_id', 'status'],
                'aggregations'     => ['total_amount', 'subtotal', 'tax_amount'],
            ],
            'stores' => [
                'label'   => 'Stores',
                'columns' => ['id', 'name', 'code', 'category', 'rank', 'is_active', 'latitude', 'longitude', 'created_at'],
                'group_by_options' => ['category', 'rank'],
                'aggregations'     => [],
            ],
            'products' => [
                'label'   => 'Products',
                'columns' => ['id', 'name', 'sku', 'barcode', 'base_price', 'is_active', 'created_at'],
                'group_by_options' => ['product_category_id'],
                'aggregations'     => ['base_price'],
            ],
            'route_instances' => [
                'label'   => 'Route Instances',
                'columns' => ['id', 'route_plan_id', 'user_id', 'status', 'started_at', 'completed_at', 'total_distance_km', 'created_at'],
                'group_by_options' => ['user_id', 'status'],
                'aggregations'     => ['total_distance_km'],
            ],
            'pos_transactions' => [
                'label'   => 'POS Transactions',
                'columns' => ['id', 'transaction_number', 'store_id', 'user_id', 'type', 'status', 'subtotal', 'tax_amount', 'total_amount', 'created_at'],
                'group_by_options' => ['store_id', 'user_id', 'type', 'status'],
                'aggregations'     => ['total_amount', 'subtotal', 'tax_amount'],
            ],
        ];
    }

    // ─── Export Helpers ───────────────────────────────────────────────

    /**
     * Export report data as Excel.
     */
    public function exportExcel(array $report): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($report['title'] ?? 'Report', 0, 31));

        $rows = $report['rows'] ?? [];
        if (empty($rows)) {
            $sheet->setCellValue('A1', 'No data');
            return $this->streamExcel($spreadsheet, $report['title'] ?? 'report');
        }

        // Header row
        $headers = array_keys($rows[0]);
        foreach ($headers as $colIdx => $header) {
            $cell = $sheet->getCellByColumnAndRow($colIdx + 1, 1);
            $cell->setValue(ucwords(str_replace('_', ' ', $header)));
            $cell->getStyle()->getFont()->setBold(true);
        }

        // Data rows
        foreach ($rows as $rowIdx => $row) {
            $colIdx = 1;
            foreach ($row as $value) {
                $sheet->setCellValue([$colIdx, $rowIdx + 2], is_array($value) ? json_encode($value) : $value);
                $colIdx++;
            }
        }

        // Auto-size columns
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return $this->streamExcel($spreadsheet, $report['title'] ?? 'report');
    }

    /**
     * Export report data as PDF.
     */
    public function exportPdf(array $report)
    {
        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
        ])->setPaper('a4', 'landscape');

        $filename = str_replace(' ', '_', $report['title'] ?? 'report') . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    // ─── Private Helpers ──────────────────────────────────────────────

    private function resolveEntity(string $entity)
    {
        return match ($entity) {
            'sales_orders'     => SalesOrder::query(),
            'stores'           => Store::query(),
            'products'         => Product::query(),
            'route_instances'  => RouteInstance::query(),
            'pos_transactions' => PosTransaction::query(),
            default            => SalesOrder::query(),
        };
    }

    private function resolveAggregations(string $entity, array $columns): array
    {
        $entityMeta = $this->reportEntities()[$entity] ?? [];
        $aggs = $entityMeta['aggregations'] ?? [];
        $selects = [];
        foreach ($aggs as $col) {
            if (in_array($col, $columns) || empty($columns)) {
                $selects[] = DB::raw("SUM({$col}) as total_{$col}");
                $selects[] = DB::raw("AVG({$col}) as avg_{$col}");
                $selects[] = DB::raw("COUNT(*) as row_count");
            }
        }
        return $selects ?: [DB::raw('COUNT(*) as row_count')];
    }

    private function loadRelations($query, string $entity): void
    {
        match ($entity) {
            'sales_orders'     => $query->with(['store:id,name,code', 'salesperson:id,first_name,last_name']),
            'pos_transactions' => $query->with(['store:id,name,code', 'user:id,first_name,last_name']),
            'route_instances'  => $query->with(['user:id,first_name,last_name', 'routePlan:id,name']),
            default            => null,
        };
    }

    private function streamExcel(Spreadsheet $spreadsheet, string $title): StreamedResponse
    {
        $filename = str_replace(' ', '_', $title) . '_' . now()->format('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
