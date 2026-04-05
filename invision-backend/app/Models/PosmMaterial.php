<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosmMaterial extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'type',
        'sku',
        'quantity_available',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_available' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function placements(): HasMany
    {
        return $this->hasMany(PosmPlacement::class);
    }
}
