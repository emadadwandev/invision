<?php

namespace App\Models;

use App\Enums\StoreCategory;
use App\Enums\StoreRank;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'qr_code',
        'category',
        'rank',
        'gps_latitude',
        'gps_longitude',
        'address',
        'area_id',
        'profile',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => StoreCategory::class,
            'rank' => StoreRank::class,
            'gps_latitude' => 'decimal:8',
            'gps_longitude' => 'decimal:8',
            'profile' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(StoreContact::class);
    }

    public function primaryContact(): HasMany
    {
        return $this->hasMany(StoreContact::class)->where('is_primary', true);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_products')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
