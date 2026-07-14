import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/services/location_service.dart';
import '../../../routes/presentation/providers/route_providers.dart';

// ─── Location Service Provider ─────────────────────────────

final locationServiceProvider = Provider<LocationService>((ref) {
  return LocationService();
});

// ─── Current Position Provider ─────────────────────────────

final currentPositionProvider = FutureProvider.autoDispose<Position?>((ref) async {
  final locationService = ref.read(locationServiceProvider);
  return locationService.getCurrentPosition();
});

// ─── Geo-Fence Settings Provider ───────────────────────────

final geofenceSettingsProvider =
    FutureProvider.autoDispose<GeofenceSettings>((ref) async {
  final repo = ref.read(gpsTrackingRepositoryProvider);
  return repo.getGeofenceSettings();
});

// ─── Active Duty Provider ──────────────────────────────────

final activeDutyProvider =
    FutureProvider.autoDispose<DutyStatus>((ref) async {
  final repo = ref.read(gpsTrackingRepositoryProvider);
  return repo.getActiveDuty();
});

// ─── GPS Tracking Controller ───────────────────────────────

final gpsTrackingControllerProvider =
    StateNotifierProvider<GpsTrackingController, GpsTrackingState>((ref) {
  return GpsTrackingController(ref);
});

class GpsTrackingState {
  final bool isTracking;
  final bool isDutyActive;
  final Position? lastPosition;
  final int pendingLogs;
  final String? error;

  const GpsTrackingState({
    this.isTracking = false,
    this.isDutyActive = false,
    this.lastPosition,
    this.pendingLogs = 0,
    this.error,
  });

  GpsTrackingState copyWith({
    bool? isTracking,
    bool? isDutyActive,
    Position? lastPosition,
    int? pendingLogs,
    String? error,
  }) {
    return GpsTrackingState(
      isTracking: isTracking ?? this.isTracking,
      isDutyActive: isDutyActive ?? this.isDutyActive,
      lastPosition: lastPosition ?? this.lastPosition,
      pendingLogs: pendingLogs ?? this.pendingLogs,
      error: error,
    );
  }
}

class GpsTrackingController extends StateNotifier<GpsTrackingState> {
  final Ref _ref;
  final List<Map<String, dynamic>> _pendingLogs = [];
  Timer? _batchTimer;

  GpsTrackingController(this._ref) : super(const GpsTrackingState());

  /// Start duty and begin GPS tracking.
  Future<void> startDuty() async {
    try {
      final locationService = _ref.read(locationServiceProvider);

      final hasPermission = await locationService.ensurePermissions();
      if (!hasPermission) {
        state = state.copyWith(error: 'Location permission denied');
        return;
      }

      final position = await locationService.getCurrentPosition();

      // Start duty on API
      final repo = _ref.read(gpsTrackingRepositoryProvider);
      await repo.startDuty(
        latitude: position?.latitude,
        longitude: position?.longitude,
      );

      // Start GPS tracking
      locationService.startTracking(
        onPosition: _onPositionUpdate,
        distanceFilter: 10,
        intervalMs: 30000,
      );

      // Start batch upload timer (every 60 seconds)
      _batchTimer = Timer.periodic(
        const Duration(seconds: 60),
        (_) => _flushPendingLogs(),
      );

      state = state.copyWith(
        isTracking: true,
        isDutyActive: true,
        lastPosition: position,
        error: null,
      );
    } catch (e) {
      state = state.copyWith(error: 'Failed to start duty: $e');
    }
  }

  /// End duty and stop GPS tracking.
  Future<void> endDuty() async {
    try {
      final locationService = _ref.read(locationServiceProvider);
      final position = locationService.lastPosition;

      // Flush remaining logs
      await _flushPendingLogs();

      // Stop tracking
      locationService.stopTracking();
      _batchTimer?.cancel();
      _batchTimer = null;

      // End duty on API
      final repo = _ref.read(gpsTrackingRepositoryProvider);
      await repo.endDuty(
        latitude: position?.latitude,
        longitude: position?.longitude,
      );

      state = state.copyWith(
        isTracking: false,
        isDutyActive: false,
        pendingLogs: 0,
        error: null,
      );
    } catch (e) {
      state = state.copyWith(error: 'Failed to end duty: $e');
    }
  }

  void _onPositionUpdate(Position position) {
    _pendingLogs.add({
      'latitude': position.latitude,
      'longitude': position.longitude,
      'accuracy_meters': position.accuracy,
      'speed_kmh': (position.speed * 3.6), // m/s to km/h
      'bearing': position.heading,
      'recorded_at': DateTime.now().toIso8601String(),
    });

    state = state.copyWith(
      lastPosition: position,
      pendingLogs: _pendingLogs.length,
    );

    // Auto-flush if batch size exceeded
    if (_pendingLogs.length >= 10) {
      _flushPendingLogs();
    }
  }

  Future<void> _flushPendingLogs() async {
    if (_pendingLogs.isEmpty) return;

    final logsToSend = List<Map<String, dynamic>>.from(_pendingLogs);
    _pendingLogs.clear();

    try {
      final routeRepo = _ref.read(routeRepositoryProvider);
      await routeRepo.batchLogGps(logsToSend);
      state = state.copyWith(pendingLogs: _pendingLogs.length);
    } catch (e) {
      // Re-add failed logs for retry
      _pendingLogs.insertAll(0, logsToSend);
      state = state.copyWith(pendingLogs: _pendingLogs.length);
    }
  }

  @override
  void dispose() {
    _batchTimer?.cancel();
    _ref.read(locationServiceProvider).stopTracking();
    super.dispose();
  }
}

// ─── Repository ────────────────────────────────────────────

final gpsTrackingRepositoryProvider =
    Provider<GpsTrackingRepository>((ref) {
  return GpsTrackingRepository(ref.watch(apiClientProvider));
});

class GpsTrackingRepository {
  final ApiClient _client;

  GpsTrackingRepository(this._client);

  Future<GeofenceSettings> getGeofenceSettings() async {
    final response = await _client.dio.get('/geofence/settings');
    return GeofenceSettings.fromJson(response.data as Map<String, dynamic>);
  }

  Future<DutyStatus> getActiveDuty() async {
    final response = await _client.dio.get('/duty/active');
    return DutyStatus.fromJson(response.data as Map<String, dynamic>);
  }

  Future<void> startDuty({double? latitude, double? longitude}) async {
    await _client.dio.post('/duty/start', data: {
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
    });
  }

  Future<void> endDuty({double? latitude, double? longitude}) async {
    await _client.dio.post('/duty/end', data: {
      if (latitude != null) 'latitude': latitude,
      if (longitude != null) 'longitude': longitude,
    });
  }
}

// ─── Models ────────────────────────────────────────────────

class GeofenceSettings {
  final int checkinRadiusMeters;
  final int checkoutRadiusMeters;
  final bool enforceGeofence;
  final int gpsTrackingIntervalSeconds;
  final int gpsBatchSize;
  final bool requireGpsForCheckin;
  final bool autoCheckoutOnLeave;
  final int autoCheckoutDistanceMeters;

  const GeofenceSettings({
    this.checkinRadiusMeters = 50,
    this.checkoutRadiusMeters = 100,
    this.enforceGeofence = true,
    this.gpsTrackingIntervalSeconds = 30,
    this.gpsBatchSize = 10,
    this.requireGpsForCheckin = true,
    this.autoCheckoutOnLeave = false,
    this.autoCheckoutDistanceMeters = 200,
  });

  factory GeofenceSettings.fromJson(Map<String, dynamic> json) {
    return GeofenceSettings(
      checkinRadiusMeters: json['checkin_radius_meters'] as int? ?? 50,
      checkoutRadiusMeters: json['checkout_radius_meters'] as int? ?? 100,
      enforceGeofence: json['enforce_geofence'] as bool? ?? true,
      gpsTrackingIntervalSeconds:
          json['gps_tracking_interval_seconds'] as int? ?? 30,
      gpsBatchSize: json['gps_batch_size'] as int? ?? 10,
      requireGpsForCheckin: json['require_gps_for_checkin'] as bool? ?? true,
      autoCheckoutOnLeave: json['auto_checkout_on_leave'] as bool? ?? false,
      autoCheckoutDistanceMeters:
          json['auto_checkout_distance_meters'] as int? ?? 200,
    );
  }
}

class DutyStatus {
  final bool active;
  final Map<String, dynamic>? session;

  const DutyStatus({this.active = false, this.session});

  factory DutyStatus.fromJson(Map<String, dynamic> json) {
    return DutyStatus(
      active: json['active'] as bool? ?? false,
      session: json['session'] as Map<String, dynamic>?,
    );
  }
}
