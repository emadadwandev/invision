<?php

namespace App\Models;

use App\Enums\PosmCondition;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosmPlacement extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'posm_material_id',
        'store_id',
        'placed_by',
        'placed_at',
        'condition',
        'photo_path',
        'last_checked_at',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'condition' => PosmCondition::class,
            'placed_at' => 'date',
            'last_checked_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(PosmMaterial::class, 'posm_material_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function placedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function checkLogs(): HasMany
    {
        return $this->hasMany(PosmCheckLog::class)->latest();
    }
}
