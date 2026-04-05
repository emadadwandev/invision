<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function priceLevels(): HasMany
    {
        return $this->hasMany(ProductPriceLevel::class);
    }

    public function currentPrice(): HasMany
    {
        return $this->hasMany(ProductPriceLevel::class)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->orderByDesc('effective_from');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_products')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
