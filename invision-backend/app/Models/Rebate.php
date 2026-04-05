<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rebate extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'type',
        'value',
        'min_quantity',
        'max_quantity',
        'start_date',
        'end_date',
        'is_active',
        'product_id',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active
            && $this->start_date->lte(now())
            && $this->end_date->gte(now());
    }
}
