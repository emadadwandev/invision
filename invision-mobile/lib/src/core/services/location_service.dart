import 'dart:async';
import 'package:geolocator/geolocator.dart';

class LocationService {
  static final LocationService _instance = LocationService._internal();
  factory LocationService() => _instance;
  LocationService._internal();

  StreamSubscription<Position>? _positionSubscription;
  Position? _lastPosition;

  Position? get lastPosition => _lastPosition;

  /// Check and request location permissions.
  Future<bool> ensurePermissions() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      return false;
    }

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        return false;
      }
    }

    if (permission == LocationPermission.deniedForever) {
      return false;
    }

    return true;
  }

  /// Get current position with high accuracy.
  Future<Position?> getCurrentPosition() async {
    final hasPermission = await ensurePermissions();
    if (!hasPermission) return null;

    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 0,
        ),
      );
      _lastPosition = position;
      return position;
    } catch (e) {
      return null;
    }
  }

  /// Calculate distance between two points in meters.
  static double distanceBetween(
    double startLat,
    double startLng,
    double endLat,
    double endLng,
  ) {
    return Geolocator.distanceBetween(startLat, startLng, endLat, endLng);
  }

  /// Check if user is within geo-fence radius of a store.
  static bool isWithinGeoFence({
    required double userLat,
    required double userLng,
    required double storeLat,
    required double storeLng,
    required double radiusMeters,
  }) {
    final distance =
        Geolocator.distanceBetween(userLat, userLng, storeLat, storeLng);
    return distance <= radiusMeters;
  }

  /// Start continuous position tracking.
  void startTracking({
    required void Function(Position) onPosition,
    int distanceFilter = 10,
    int intervalMs = 30000,
  }) {
    stopTracking();

    _positionSubscription = Geolocator.getPositionStream(
      locationSettings: AndroidSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: distanceFilter,
        intervalDuration: Duration(milliseconds: intervalMs),
        foregroundNotificationConfig: const ForegroundNotificationConfig(
          notificationTitle: 'Invision GPS Tracking',
          notificationText: 'Tracking your location during duty hours',
          enableWakeLock: true,
        ),
      ),
    ).listen((position) {
      _lastPosition = position;
      onPosition(position);
    });
  }

  /// Stop continuous tracking.
  void stopTracking() {
    _positionSubscription?.cancel();
    _positionSubscription = null;
  }

  /// Check if tracking is active.
  bool get isTracking => _positionSubscription != null;

  /// Open device location settings.
  Future<bool> openLocationSettings() async {
    return Geolocator.openLocationSettings();
  }

  /// Open app settings (for permission management).
  Future<bool> openAppSettings() async {
    return Geolocator.openAppSettings();
  }
}
