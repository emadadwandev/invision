<?php

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;

class GeofenceSetting extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'checkin_radius_meters',
        'checkout_radius_meters',
        'enforce_geofence',
        'gps_tracking_interval_seconds',
        'gps_batch_size',
        'require_gps_for_checkin',
        'auto_checkout_on_leave',
        'auto_checkout_distance_meters',
    ];

    protected function casts(): array
    {
        return [
            'enforce_geofence' => 'boolean',
            'require_gps_for_checkin' => 'boolean',
            'auto_checkout_on_leave' => 'boolean',
        ];
    }
}
