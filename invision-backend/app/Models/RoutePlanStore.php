<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePlanStore extends Model
{
    protected $fillable = [
        'route_plan_id',
        'store_id',
        'visit_order',
        'expected_duration_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_order' => 'integer',
            'expected_duration_minutes' => 'integer',
        ];
    }

    public function routePlan(): BelongsTo
    {
        return $this->belongsTo(RoutePlan::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
