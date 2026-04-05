import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_map_cancellable_tile_provider/flutter_map_cancellable_tile_provider.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:latlong2/latlong.dart';

import '../../../../core/theme/app_theme.dart';
import '../providers/command_center_providers.dart';

class UserActivityPage extends ConsumerWidget {
  const UserActivityPage({super.key, required this.userId});

  final int userId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final activityAsync = ref.watch(userActivityProvider(userId));

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('User Activity',
            style: Theme.of(context).textTheme.headlineMedium
                ?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: activityAsync.when(
        data: (activity) {
          final trailPoints = activity.gpsTrail
              .map((p) => LatLng(p.latitude, p.longitude))
              .toList();

          return Column(
            children: [
              // User info card
              Container(
                padding: const EdgeInsets.all(16),
                color: AppColors.surfaceContainerLow,
                child: Row(
                  children: [
                    Container(
                      width: 44, height: 44,
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerHigh,
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: const Center(
                        child: Icon(Icons.person,
                            size: 22, color: AppColors.onSurfaceVariant),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            activity.user.name,
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(color: AppColors.onSurface),
                          ),
                          Text(activity.user.role,
                              style: const TextStyle(
                                  color: AppColors.onSurfaceVariant)),
                        ],
                      ),
                    ),
                    if (activity.route != null)
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.surfaceContainerHigh,
                          borderRadius: BorderRadius.circular(100),
                        ),
                        child: Text(
                          activity.route!.planName ?? 'Active Route',
                          style: const TextStyle(
                              fontSize: 11,
                              color: AppColors.onSurfaceVariant,
                              fontWeight: FontWeight.w600),
                        ),
                      ),
                  ],
                ),
              ),
              // Map with GPS trail
              Expanded(
                flex: 3,
                child: FlutterMap(
                  options: MapOptions(
                    initialCenter: trailPoints.isNotEmpty
                        ? trailPoints.last
                        : const LatLng(33.8938, 35.5018),
                    initialZoom: 14,
                  ),
                  children: [
                    TileLayer(
                      urlTemplate:
                          'https://api.mapbox.com/styles/v1/mapbox/streets-v12/tiles/{z}/{x}/{y}@2x?access_token={accessToken}',
                      additionalOptions: const {
                        'accessToken': String.fromEnvironment(
                          'MAPBOX_TOKEN',
                          defaultValue: '',
                        ),
                      },
                      userAgentPackageName: 'com.invision.mobile',
                      tileProvider: CancellableNetworkTileProvider(),
                      fallbackUrl:
                          'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    ),
                    if (trailPoints.length > 1)
                      PolylineLayer(
                        polylines: [
                          Polyline(
                            points: trailPoints,
                            color: AppColors.primaryContainer,
                            strokeWidth: 3,
                          ),
                        ],
                      ),
                    if (trailPoints.isNotEmpty)
                      MarkerLayer(
                        markers: [
                          // Start point
                          Marker(
                            point: trailPoints.first,
                            width: 28,
                            height: 28,
                            child: const Icon(
                              Icons.trip_origin_rounded,
                              color: AppColors.secondary,
                              size: 28,
                            ),
                          ),
                          // Current/last point
                          if (trailPoints.length > 1)
                            Marker(
                              point: trailPoints.last,
                              width: 32,
                              height: 32,
                              child: Container(
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.primaryContainer,
                                  border: Border.all(
                                    color: Colors.white,
                                    width: 2,
                                  ),
                                ),
                                child: const Icon(
                                  Icons.person,
                                  color: Colors.white,
                                  size: 18,
                                ),
                              ),
                            ),
                        ],
                      ),
                  ],
                ),
              ),
              // Visits list
              if (activity.visits.isNotEmpty)
                Expanded(
                  flex: 2,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                        child: Text(
                          'Store Visits (${activity.visits.length})',
                          style: Theme.of(context).textTheme.titleSmall
                              ?.copyWith(color: AppColors.onSurface,
                                  fontWeight: FontWeight.w700),
                        ),
                      ),
                      Expanded(
                        child: ListView.builder(
                          itemCount: activity.visits.length,
                          itemBuilder: (context, index) {
                            final visit = activity.visits[index];
                            return Container(
                              margin: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 3),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 10),
                              decoration: BoxDecoration(
                                color: AppColors.surfaceContainerLowest,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    visit.status == 'completed'
                                        ? Icons.check_circle_rounded
                                        : visit.status == 'in_progress'
                                            ? Icons.radio_button_checked_rounded
                                            : Icons.radio_button_unchecked_rounded,
                                    color: visit.status == 'completed'
                                        ? AppColors.secondary
                                        : visit.status == 'in_progress'
                                            ? AppColors.primaryContainer
                                            : AppColors.outline,
                                    size: 20,
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          visit.storeName ?? 'Store',
                                          style: const TextStyle(
                                              fontWeight: FontWeight.w600,
                                              color: AppColors.onSurface),
                                        ),
                                        Text(
                                          visit.status,
                                          style: const TextStyle(
                                              fontSize: 11,
                                              color: AppColors.onSurfaceVariant),
                                        ),
                                      ],
                                    ),
                                  ),
                                  if (visit.durationMinutes != null)
                                    Text(
                                      '${visit.durationMinutes} min',
                                      style: const TextStyle(
                                          fontSize: 11,
                                          color: AppColors.onSurfaceVariant),
                                    ),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          );
        },
        loading: () => const Center(
            child: CircularProgressIndicator(color: AppColors.primary)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}
