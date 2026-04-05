<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GeoFenceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeoFenceController extends Controller
{
    public function __construct(private readonly GeoFenceService $geoFenceService) {}

    public function settings(): View
    {
        $settings = $this->geoFenceService->getSettings();

        return view('pages.geofence.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'checkin_radius_meters' => 'required|integer|min:5|max:1000',
            'checkout_radius_meters' => 'required|integer|min:5|max:2000',
            'enforce_geofence' => 'boolean',
            'gps_tracking_interval_seconds' => 'required|integer|min:5|max:300',
            'gps_batch_size' => 'required|integer|min:1|max:100',
            'require_gps_for_checkin' => 'boolean',
            'auto_checkout_on_leave' => 'boolean',
            'auto_checkout_distance_meters' => 'required|integer|min:50|max:5000',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['enforce_geofence'] = $request->boolean('enforce_geofence');
        $validated['require_gps_for_checkin'] = $request->boolean('require_gps_for_checkin');
        $validated['auto_checkout_on_leave'] = $request->boolean('auto_checkout_on_leave');

        $this->geoFenceService->updateSettings($validated);

        return redirect()->route('geofence.settings')->with('success', 'Geo-fence settings updated.');
    }

    public function dutySessions(Request $request): View
    {
        $sessions = $this->geoFenceService->listDutySessions(
            $request->only(['user_id', 'date_from', 'date_to']),
            15,
        );

        return view('pages.geofence.duty-sessions', compact('sessions'));
    }
}
