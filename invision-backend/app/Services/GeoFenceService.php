<?php

namespace App\Services;

use App\Events\DutyStatusChanged;
use App\Models\DutySession;
use App\Models\GeofenceSetting;
use App\Models\GpsTrackingLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class GeoFenceService
{
    /**
     * Calculate distance between two GPS points using Haversine formula.
     * Returns distance in meters.
     */
    public static function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Validate if coordinates are within geo-fence radius of a store.
     */
    public function validateGeoFence(
        float $userLat,
        float $userLng,
        float $storeLat,
        float $storeLng,
        ?int $radiusMeters = null
    ): array {
        $settings = $this->getSettings();
        $radius = $radiusMeters ?? $settings->checkin_radius_meters;

        $distance = self::calculateDistance($userLat, $userLng, $storeLat, $storeLng);

        return [
            'within_geofence' => $distance <= $radius,
            'distance_meters' => round($distance, 2),
            'radius_meters' => $radius,
            'enforce' => $settings->enforce_geofence,
        ];
    }

    /**
     * Get geo-fence settings for current tenant.
     */
    public function getSettings(): GeofenceSetting
    {
        return GeofenceSetting::first() ?? new GeofenceSetting([
            'checkin_radius_meters' => 50,
            'checkout_radius_meters' => 100,
            'enforce_geofence' => true,
            'gps_tracking_interval_seconds' => 30,
            'gps_batch_size' => 10,
            'require_gps_for_checkin' => true,
            'auto_checkout_on_leave' => false,
            'auto_checkout_distance_meters' => 200,
        ]);
    }

    /**
     * Update geo-fence settings.
     */
    public function updateSettings(array $data): GeofenceSetting
    {
        $settings = GeofenceSetting::first();

        if ($settings) {
            $settings->update($data);
        } else {
            $settings = GeofenceSetting::create($data);
        }

        return $settings;
    }

    // ─── Duty Sessions ───────────────────────────────────────

    /**
     * Start a duty session for the authenticated user.
     */
    public function startDuty(int $userId, ?float $latitude = null, ?float $longitude = null): DutySession
    {
        // End any existing active session first
        $active = DutySession::where('user_id', $userId)
            ->whereNull('ended_at')
            ->first();

        if ($active) {
            $this->endDuty($active, $latitude, $longitude);
        }

        $session = DutySession::create([
            'user_id' => $userId,
            'started_at' => now(),
            'start_latitude' => $latitude,
            'start_longitude' => $longitude,
        ]);

        // Broadcast duty started
        $user = $session->user;
        if ($user) {
            DutyStatusChanged::dispatch(
                $user->tenant_id,
                $userId,
                $user->full_name ?? '',
                true,
                $latitude,
                $longitude,
            );
        }

        return $session;
    }

    /**
     * End a duty session.
     */
    public function endDuty(DutySession $session, ?float $latitude = null, ?float $longitude = null): DutySession
    {
        $endTime = now();
        $totalMinutes = $session->started_at->diffInMinutes($endTime);

        // Count GPS logs during duty
        $totalLogs = GpsTrackingLog::where('user_id', $session->user_id)
            ->whereBetween('recorded_at', [$session->started_at, $endTime])
            ->count();

        // Calculate total distance from GPS trail
        $totalDistance = $this->calculateTrailDistance(
            $session->user_id,
            $session->started_at,
            $endTime
        );

        $session->update([
            'ended_at' => $endTime,
            'end_latitude' => $latitude,
            'end_longitude' => $longitude,
            'total_minutes' => $totalMinutes,
            'total_gps_logs' => $totalLogs,
            'total_distance_km' => round($totalDistance / 1000, 2),
        ]);

        // Broadcast duty ended
        $user = $session->user;
        if ($user) {
            DutyStatusChanged::dispatch(
                $user->tenant_id,
                $session->user_id,
                $user->full_name ?? '',
                false,
                $latitude,
                $longitude,
            );
        }

        return $session;
    }

    /**
     * Get the current active duty session for a user.
     */
    public function getActiveDuty(int $userId): ?DutySession
    {
        return DutySession::where('user_id', $userId)
            ->whereNull('ended_at')
            ->first();
    }

    /**
     * List duty sessions for a user.
     */
    public function listDutySessions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DutySession::with('user');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('started_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('started_at', '<=', $filters['date_to']);
        }

        return $query->latest('started_at')->paginate($perPage);
    }

    /**
     * Calculate total distance of a GPS trail between two timestamps.
     */
    private function calculateTrailDistance(int $userId, Carbon $from, Carbon $to): float
    {
        $logs = GpsTrackingLog::where('user_id', $userId)
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude']);

        $totalDistance = 0;
        $previous = null;

        foreach ($logs as $log) {
            if ($previous) {
                $totalDistance += self::calculateDistance(
                    (float) $previous->latitude,
                    (float) $previous->longitude,
                    (float) $log->latitude,
                    (float) $log->longitude
                );
            }
            $previous = $log;
        }

        return $totalDistance;
    }
}
