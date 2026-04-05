<?php

namespace App\Models;

use App\Enums\StockMovementType;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
