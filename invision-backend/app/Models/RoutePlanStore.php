<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePlanStore extends Model
{
    protected $fillable = [
        'route_plan_id',
        'store_id',
        'day_of_week',
        'week_number',
        'week_start',
        'visit_order',
        'expected_duration_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'week_number' => 'integer',
            'week_start' => 'date',
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
