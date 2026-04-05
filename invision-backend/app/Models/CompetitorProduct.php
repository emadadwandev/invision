<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitorProduct extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'competitor_id',
        'name',
        'sku',
        'barcode',
        'category',
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

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(CompetitorObservation::class);
    }
}
