<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GeoFenceException;
use App\Http\Controllers\Controller;
use App\Services\GeoFenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoFenceController extends Controller
{
    public function __construct(private readonly GeoFenceService $geoFenceService) {}

    // ─── Geo-Fence Settings ──────────────────────────────────

    public function settings(): JsonResponse
    {
        return response()->json($this->geoFenceService->getSettings());
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkin_radius_meters' => 'integer|min:5|max:1000',
            'checkout_radius_meters' => 'integer|min:5|max:2000',
            'enforce_geofence' => 'boolean',
            'gps_tracking_interval_seconds' => 'integer|min:5|max:300',
            'gps_batch_size' => 'integer|min:1|max:100',
            'require_gps_for_checkin' => 'boolean',
            'auto_checkout_on_leave' => 'boolean',
            'auto_checkout_distance_meters' => 'integer|min:50|max:5000',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $settings = $this->geoFenceService->updateSettings($validated);

        return response()->json($settings);
    }

    // ─── Geo-Fence Validation ────────────────────────────────

    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'store_latitude' => 'required|numeric|between:-90,90',
            'store_longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:1',
        ]);

        $result = $this->geoFenceService->validateGeoFence(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $validated['store_latitude'],
            (float) $validated['store_longitude'],
            $validated['radius_meters'] ?? null,
        );

        return response()->json($result);
    }

    // ─── Duty Sessions ───────────────────────────────────────

    public function startDuty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $session = $this->geoFenceService->startDuty(
            $request->user()->id,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        return response()->json($session, 201);
    }

    public function endDuty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $session = $this->geoFenceService->getActiveDuty($request->user()->id);

        if (! $session) {
            return response()->json(['message' => 'No active duty session found.'], 404);
        }

        $session = $this->geoFenceService->endDuty(
            $session,
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        return response()->json($session);
    }

    public function activeDuty(Request $request): JsonResponse
    {
        $session = $this->geoFenceService->getActiveDuty($request->user()->id);

        if (! $session) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'session' => $session,
        ]);
    }

    public function dutySessions(Request $request): JsonResponse
    {
        $sessions = $this->geoFenceService->listDutySessions(
            $request->only(['user_id', 'date_from', 'date_to']),
            $request->integer('per_page', 15),
        );

        return response()->json($sessions);
    }
}
