import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_map_cancellable_tile_provider/flutter_map_cancellable_tile_provider.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:latlong2/latlong.dart';

import '../providers/command_center_providers.dart';

class UserActivityPage extends ConsumerWidget {
  const UserActivityPage({super.key, required this.userId});

  final int userId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final activityAsync = ref.watch(userActivityProvider(userId));

    return Scaffold(
      appBar: AppBar(title: const Text('User Activity')),
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
                color: Theme.of(context).colorScheme.surfaceContainerLow,
                child: Row(
                  children: [
                    const CircleAvatar(
                      child: Icon(Icons.person),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            activity.user.name,
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          Text(activity.user.role),
                        ],
                      ),
                    ),
                    if (activity.route != null)
                      Chip(
                        label: Text(
                          activity.route!.planName ?? 'Active Route',
                          style: const TextStyle(fontSize: 12),
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
                            color: Colors.blue,
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
                              Icons.trip_origin,
                              color: Colors.green,
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
                                  color: Colors.blue,
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
                          style: Theme.of(context).textTheme.titleSmall,
                        ),
                      ),
                      Expanded(
                        child: ListView.builder(
                          itemCount: activity.visits.length,
                          itemBuilder: (context, index) {
                            final visit = activity.visits[index];
                            return ListTile(
                              dense: true,
                              leading: Icon(
                                visit.status == 'completed'
                                    ? Icons.check_circle
                                    : visit.status == 'in_progress'
                                        ? Icons.radio_button_checked
                                        : Icons.radio_button_unchecked,
                                color: visit.status == 'completed'
                                    ? Colors.green
                                    : visit.status == 'in_progress'
                                        ? Colors.blue
                                        : Colors.grey,
                                size: 20,
                              ),
                              title: Text(visit.storeName ?? 'Store'),
                              subtitle: Text(visit.status),
                              trailing: visit.durationMinutes != null
                                  ? Text('${visit.durationMinutes} min')
                                  : null,
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
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}
