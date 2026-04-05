<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesArea extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'parent_id',
        'manager_id',
        'geometry',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'geometry' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SalesArea::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(SalesArea::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SalesAreaAssignment::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'sales_area_stores');
    }
}
