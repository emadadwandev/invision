<?php

namespace App\Models;

use App\Enums\ObservationType;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorObservation extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'store_visit_id',
        'store_id',
        'user_id',
        'competitor_id',
        'competitor_product_id',
        'observation_type',
        'quantity',
        'price',
        'notes',
        'photo_path',
        'latitude',
        'longitude',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'observation_type' => ObservationType::class,
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'observed_at' => 'datetime',
        ];
    }

    public function storeVisit(): BelongsTo
    {
        return $this->belongsTo(StoreVisit::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }

    public function competitorProduct(): BelongsTo
    {
        return $this->belongsTo(CompetitorProduct::class);
    }
}
