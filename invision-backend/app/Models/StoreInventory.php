<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreInventory extends Model
{
    use HasTenant;

    protected $table = 'store_inventory';

    protected $fillable = [
        'tenant_id',
        'store_id',
        'product_id',
        'on_shelf_quantity',
        'warehouse_quantity',
        'last_counted_at',
    ];

    protected function casts(): array
    {
        return [
            'on_shelf_quantity' => 'integer',
            'warehouse_quantity' => 'integer',
            'last_counted_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id')
            ->where('store_id', $this->store_id);
    }

    public function totalQuantity(): int
    {
        return $this->on_shelf_quantity + $this->warehouse_quantity;
    }
}
