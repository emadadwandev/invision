<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsTrackingLog extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'route_instance_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'speed_kmh',
        'bearing',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'accuracy_meters' => 'decimal:2',
            'speed_kmh' => 'decimal:2',
            'bearing' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routeInstance(): BelongsTo
    {
        return $this->belongsTo(RouteInstance::class);
    }
}
