import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/services/location_service.dart';
import '../providers/gps_tracking_providers.dart';

class GeoFenceCheckPage extends ConsumerStatefulWidget {
  const GeoFenceCheckPage({
    super.key,
    required this.storeName,
    required this.storeLat,
    required this.storeLng,
  });

  final String storeName;
  final double storeLat;
  final double storeLng;

  @override
  ConsumerState<GeoFenceCheckPage> createState() => _GeoFenceCheckPageState();
}

class _GeoFenceCheckPageState extends ConsumerState<GeoFenceCheckPage> {
  bool _checking = true;
  bool _withinFence = false;
  double _distance = 0;
  int _radius = 50;
  String? _error;

  @override
  void initState() {
    super.initState();
    _checkGeoFence();
  }

  Future<void> _checkGeoFence() async {
    setState(() {
      _checking = true;
      _error = null;
    });

    try {
      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.getCurrentPosition();

      if (position == null) {
        setState(() {
          _checking = false;
          _error = 'Could not get your location. Please enable GPS.';
        });
        return;
      }

      final distance = LocationService.distanceBetween(
        position.latitude,
        position.longitude,
        widget.storeLat,
        widget.storeLng,
      );

      // Get settings for radius
      try {
        final settingsAsync = await ref.read(geofenceSettingsProvider.future);
        _radius = settingsAsync.checkinRadiusMeters;
      } catch (_) {
        _radius = 50;
      }

      setState(() {
        _checking = false;
        _distance = distance;
        _withinFence = distance <= _radius;
      });
    } catch (e) {
      setState(() {
        _checking = false;
        _error = 'GPS error: $e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Geo-Fence Check')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // Store name
            Text(
              widget.storeName,
              style: Theme.of(context).textTheme.headlineSmall,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),

            if (_checking) ...[
              const CircularProgressIndicator(),
              const SizedBox(height: 16),
              const Text('Checking your location...'),
            ] else if (_error != null) ...[
              const Icon(Icons.error_outline, size: 64, color: Colors.red),
              const SizedBox(height: 16),
              Text(
                _error!,
                style: const TextStyle(color: Colors.red),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              OutlinedButton.icon(
                onPressed: _checkGeoFence,
                icon: const Icon(Icons.refresh),
                label: const Text('Retry'),
              ),
            ] else ...[
              // Result icon
              Icon(
                _withinFence ? Icons.check_circle : Icons.cancel,
                size: 80,
                color: _withinFence ? Colors.green : Colors.red,
              ),
              const SizedBox(height: 16),
              Text(
                _withinFence
                    ? 'You are within the geo-fence!'
                    : 'You are outside the geo-fence',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      color: _withinFence ? Colors.green : Colors.red,
                    ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'Distance: ${_distance.toStringAsFixed(1)}m (Radius: ${_radius}m)',
                style: Theme.of(context).textTheme.bodyLarge,
              ),
              const SizedBox(height: 32),

              if (_withinFence)
                FilledButton.icon(
                  onPressed: () => context.pop(true),
                  icon: const Icon(Icons.login),
                  label: const Text('Proceed to Check-in'),
                  style: FilledButton.styleFrom(
                    minimumSize: const Size(double.infinity, 48),
                  ),
                )
              else ...[
                OutlinedButton.icon(
                  onPressed: _checkGeoFence,
                  icon: const Icon(Icons.refresh),
                  label: const Text('Re-check Location'),
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size(double.infinity, 48),
                  ),
                ),
                const SizedBox(height: 12),
                TextButton(
                  onPressed: () {
                    final locationService = ref.read(locationServiceProvider);
                    locationService.openLocationSettings();
                  },
                  child: const Text('Open Location Settings'),
                ),
              ],
            ],
          ],
        ),
      ),
    );
  }
}
