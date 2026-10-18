<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreVisitMetric extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'store_visit_id',
        'product_id',
        'competitor_id',
        'our_sell_out_quantity',
        'competitor_sell_out_quantity',
        'our_shelf_stock_quantity',
        'competitor_shelf_stock_quantity',
        'our_warehouse_stock_quantity',
        'competitor_warehouse_stock_quantity',
        'our_sell_through_quantity',
        'competitor_sell_through_quantity',
        'planned_sell_through_quantity',
        'planned_sell_through_date',
        'planned_sell_through_basis',
        'planned_sell_through_notes',
        'our_posm_present',
        'competitor_posm_present',
        'our_front_shop_present',
        'competitor_front_shop_present',
    ];

    protected function casts(): array
    {
        return [
            'our_sell_out_quantity' => 'integer',
            'competitor_sell_out_quantity' => 'integer',
            'our_shelf_stock_quantity' => 'integer',
            'competitor_shelf_stock_quantity' => 'integer',
            'our_warehouse_stock_quantity' => 'integer',
            'competitor_warehouse_stock_quantity' => 'integer',
            'our_sell_through_quantity' => 'integer',
            'competitor_sell_through_quantity' => 'integer',
            'planned_sell_through_quantity' => 'integer',
            'planned_sell_through_date' => 'date:Y-m-d',
            'our_posm_present' => 'boolean',
            'competitor_posm_present' => 'boolean',
            'our_front_shop_present' => 'boolean',
            'competitor_front_shop_present' => 'boolean',
        ];
    }

    public function storeVisit(): BelongsTo
    {
        return $this->belongsTo(StoreVisit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }
}
