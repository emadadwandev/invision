<?php

namespace App\Models;

use App\Enums\RouteStatus;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouteInstance extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'route_plan_id',
        'user_id',
        'route_date',
        'status',
        'started_at',
        'completed_at',
        'total_distance_km',
        'total_visits',
        'completed_visits',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'route_date' => 'date',
            'status' => RouteStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_distance_km' => 'decimal:2',
            'total_visits' => 'integer',
            'completed_visits' => 'integer',
        ];
    }

    public function routePlan(): BelongsTo
    {
        return $this->belongsTo(RoutePlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(StoreVisit::class)->orderBy('visit_order');
    }

    public function gpsLogs(): HasMany
    {
        return $this->hasMany(GpsTrackingLog::class);
    }

    public function completionPercentage(): float
    {
        if ($this->total_visits === 0) {
            return 0;
        }

        return round(($this->completed_visits / $this->total_visits) * 100, 1);
    }
}
