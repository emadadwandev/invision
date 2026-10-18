<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\StoreVisit;
use App\Models\StoreVisitMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VisitCaptureController extends Controller
{
    public function show(Request $request, StoreVisit $storeVisit): JsonResponse
    {
        $this->authorizeVisit($request, $storeVisit);

        return response()->json(['data' => $this->payload($storeVisit)]);
    }

    public function save(Request $request, StoreVisit $storeVisit): JsonResponse
    {
        $this->authorizeVisit($request, $storeVisit);

        abort_unless($storeVisit->status === VisitStatus::CheckedIn, 422, 'Visit must be checked in to save visit data.');

        $tenantId = $request->user()->tenant_id;
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:10000'],
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.product_id' => [
                'required', 'integer', 'distinct',
                Rule::exists('products', 'id')->where('tenant_id', $tenantId),
            ],
            'items.*.competitor_id' => [
                'nullable', 'integer',
                Rule::exists('competitors', 'id')->where('tenant_id', $tenantId),
            ],
            'items.*.our_sell_out_quantity' => ['required', 'integer', 'min:0'],
            'items.*.competitor_sell_out_quantity' => ['required', 'integer', 'min:0'],
            'items.*.our_shelf_stock_quantity' => ['required', 'integer', 'min:0'],
            'items.*.competitor_shelf_stock_quantity' => ['required', 'integer', 'min:0'],
            'items.*.our_warehouse_stock_quantity' => ['required', 'integer', 'min:0'],
            'items.*.competitor_warehouse_stock_quantity' => ['required', 'integer', 'min:0'],
            'items.*.our_sell_through_quantity' => ['required', 'integer', 'min:0'],
            'items.*.competitor_sell_through_quantity' => ['required', 'integer', 'min:0'],
            'items.*.planned_sell_through_quantity' => ['required', 'integer', 'min:0'],
            'items.*.planned_sell_through_date' => ['nullable', 'date'],
            'items.*.planned_sell_through_basis' => ['nullable', Rule::in(['next_week', 'offer', 'quarter_agreement', 'other'])],
            'items.*.planned_sell_through_notes' => ['nullable', 'string', 'max:1000'],
            'items.*.our_posm_present' => ['required', 'boolean'],
            'items.*.competitor_posm_present' => ['required', 'boolean'],
            'items.*.our_front_shop_present' => ['required', 'boolean'],
            'items.*.competitor_front_shop_present' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($storeVisit, $validated, $tenantId): void {
            $storeVisit->update(['notes' => $validated['remarks'] ?? null]);
            $storeVisit->metrics()->delete();

            foreach ($validated['items'] as $item) {
                StoreVisitMetric::create([
                    ...$item,
                    'tenant_id' => $tenantId,
                    'store_visit_id' => $storeVisit->id,
                ]);
            }
        });

        return response()->json(['data' => $this->payload($storeVisit->fresh())]);
    }

    private function authorizeVisit(Request $request, StoreVisit $visit): void
    {
        abort_unless(
            (int) $visit->tenant_id === (int) $request->user()->tenant_id
                && (int) $visit->user_id === (int) $request->user()->id,
            403,
            'You cannot update this store visit.',
        );
    }

    private function payload(StoreVisit $visit): array
    {
        $visit->load(['store', 'metrics.product', 'metrics.competitor']);

        return [
            'visit_id' => $visit->id,
            'store' => $visit->store?->name,
            'remarks' => $visit->notes,
            'items' => $visit->metrics->map(fn (StoreVisitMetric $metric) => [
                'product_id' => $metric->product_id,
                'product_name' => $metric->product?->name,
                'competitor_id' => $metric->competitor_id,
                'competitor_name' => $metric->competitor?->name,
                'our_sell_out_quantity' => $metric->our_sell_out_quantity,
                'competitor_sell_out_quantity' => $metric->competitor_sell_out_quantity,
                'our_shelf_stock_quantity' => $metric->our_shelf_stock_quantity,
                'competitor_shelf_stock_quantity' => $metric->competitor_shelf_stock_quantity,
                'our_warehouse_stock_quantity' => $metric->our_warehouse_stock_quantity,
                'competitor_warehouse_stock_quantity' => $metric->competitor_warehouse_stock_quantity,
                'our_sell_through_quantity' => $metric->our_sell_through_quantity,
                'competitor_sell_through_quantity' => $metric->competitor_sell_through_quantity,
                'planned_sell_through_quantity' => $metric->planned_sell_through_quantity,
                'planned_sell_through_date' => $metric->planned_sell_through_date?->format('Y-m-d'),
                'planned_sell_through_basis' => $metric->planned_sell_through_basis,
                'planned_sell_through_notes' => $metric->planned_sell_through_notes,
                'our_posm_present' => $metric->our_posm_present,
                'competitor_posm_present' => $metric->competitor_posm_present,
                'our_front_shop_present' => $metric->our_front_shop_present,
                'competitor_front_shop_present' => $metric->competitor_front_shop_present,
            ])->values(),
        ];
    }
}
