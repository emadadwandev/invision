<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DutySession extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'started_at',
        'ended_at',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'total_minutes',
        'total_gps_logs',
        'total_distance_km',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'start_latitude' => 'decimal:8',
            'start_longitude' => 'decimal:8',
            'end_latitude' => 'decimal:8',
            'end_longitude' => 'decimal:8',
            'total_distance_km' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gpsLogs(): HasMany
    {
        return $this->hasMany(GpsTrackingLog::class, 'user_id', 'user_id')
            ->whereBetween('recorded_at', [$this->started_at, $this->ended_at ?? now()]);
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}
