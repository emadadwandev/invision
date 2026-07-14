import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/services/location_service.dart';
import '../../../../core/theme/app_theme.dart';
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Geo-Fence Check',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Text(widget.storeName,
                style: Theme.of(context).textTheme.headlineSmall
                    ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700),
                textAlign: TextAlign.center),
            const SizedBox(height: 32),
            if (_checking) ...[
              const CircularProgressIndicator(color: AppColors.primary),
              const SizedBox(height: 16),
              const Text('Checking your location...',
                  style: TextStyle(color: AppColors.onSurfaceVariant)),
            ] else if (_error != null) ...[
              Container(
                width: 64, height: 64,
                decoration: BoxDecoration(
                  color: AppColors.errorContainer,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(Icons.error_outline_rounded, size: 32, color: AppColors.error),
              ),
              const SizedBox(height: 16),
              Text(_error!, style: const TextStyle(color: AppColors.error),
                  textAlign: TextAlign.center),
              const SizedBox(height: 24),
              GestureDetector(
                onTap: _checkGeoFence,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLow,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.outlineVariant),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.refresh_rounded, size: 16, color: AppColors.primary),
                      SizedBox(width: 6),
                      Text('Retry', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700)),
                    ],
                  ),
                ),
              ),
            ] else ...[
              Container(
                width: 80, height: 80,
                decoration: BoxDecoration(
                  color: _withinFence
                      ? AppColors.secondary.withOpacity(0.12)
                      : AppColors.errorContainer,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Icon(
                  _withinFence ? Icons.check_circle_rounded : Icons.cancel_rounded,
                  size: 48,
                  color: _withinFence ? AppColors.secondary : AppColors.error,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                _withinFence
                    ? 'You are within the geo-fence!'
                    : 'You are outside the geo-fence',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      color: _withinFence ? AppColors.secondary : AppColors.error,
                      fontWeight: FontWeight.w700,
                    ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'Distance: ${_distance.toStringAsFixed(1)}m (Radius: ${_radius}m)',
                style: Theme.of(context).textTheme.bodyLarge
                    ?.copyWith(color: AppColors.onSurfaceVariant),
              ),
              const SizedBox(height: 32),
              if (_withinFence)
                GestureDetector(
                  onTap: () => context.pop(true),
                  child: Container(
                    width: double.infinity, height: 50,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                          colors: [AppColors.primary, AppColors.primaryContainer]),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    alignment: Alignment.center,
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.login_rounded, color: Colors.white, size: 18),
                        SizedBox(width: 6),
                        Text('Proceed to Check-in',
                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                      ],
                    ),
                  ),
                )
              else ...[
                GestureDetector(
                  onTap: _checkGeoFence,
                  child: Container(
                    width: double.infinity, height: 50,
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerLow,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.outlineVariant),
                    ),
                    alignment: Alignment.center,
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.refresh_rounded, size: 16, color: AppColors.primary),
                        SizedBox(width: 6),
                        Text('Re-check Location',
                            style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700)),
                      ],
                    ),
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
