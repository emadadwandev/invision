<?php

namespace App\Models;

use App\Enums\VisitStatus;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreVisit extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'route_instance_id',
        'store_id',
        'user_id',
        'visit_order',
        'status',
        'checked_in_at',
        'checkin_latitude',
        'checkin_longitude',
        'checkin_qr_code',
        'checkin_distance_meters',
        'checked_out_at',
        'checkout_latitude',
        'checkout_longitude',
        'duration_minutes',
        'notes',
        'skip_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => VisitStatus::class,
            'visit_order' => 'integer',
            'checked_in_at' => 'datetime',
            'checkin_latitude' => 'decimal:8',
            'checkin_longitude' => 'decimal:8',
            'checkin_distance_meters' => 'decimal:2',
            'checked_out_at' => 'datetime',
            'checkout_latitude' => 'decimal:8',
            'checkout_longitude' => 'decimal:8',
            'duration_minutes' => 'integer',
        ];
    }

    public function routeInstance(): BelongsTo
    {
        return $this->belongsTo(RouteInstance::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate distance between check-in GPS and store GPS in meters.
     */
    public static function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2,
    ): float {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
