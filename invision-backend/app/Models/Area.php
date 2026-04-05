<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'sector_id',
        'name',
        'gps_latitude',
        'gps_longitude',
        'radius_meters',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gps_latitude' => 'decimal:8',
            'gps_longitude' => 'decimal:8',
            'radius_meters' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function streets(): HasMany
    {
        return $this->hasMany(Street::class);
    }
}
