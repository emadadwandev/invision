import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/gps_tracking_providers.dart';

class DutyTrackingPage extends ConsumerWidget {
  const DutyTrackingPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final trackingState = ref.watch(gpsTrackingControllerProvider);
    final activeDutyAsync = ref.watch(activeDutyProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Duty Tracking')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Status Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    Icon(
                      trackingState.isDutyActive
                          ? Icons.gps_fixed
                          : Icons.gps_off,
                      size: 64,
                      color: trackingState.isDutyActive
                          ? Colors.green
                          : Colors.grey,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      trackingState.isDutyActive
                          ? 'On Duty'
                          : 'Off Duty',
                      style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: trackingState.isDutyActive
                                ? Colors.green
                                : Colors.grey,
                          ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      trackingState.isDutyActive
                          ? 'GPS tracking is active'
                          : 'Start duty to begin GPS tracking',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: Colors.grey[600],
                          ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // GPS Stats
            if (trackingState.isDutyActive) ...[
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'GPS Status',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 12),
                      _statusRow(
                        context,
                        'Tracking',
                        trackingState.isTracking ? 'Active' : 'Paused',
                        trackingState.isTracking
                            ? Colors.green
                            : Colors.orange,
                      ),
                      const Divider(),
                      _statusRow(
                        context,
                        'Last Position',
                        trackingState.lastPosition != null
                            ? '${trackingState.lastPosition!.latitude.toStringAsFixed(6)}, ${trackingState.lastPosition!.longitude.toStringAsFixed(6)}'
                            : 'Acquiring...',
                        null,
                      ),
                      const Divider(),
                      _statusRow(
                        context,
                        'Pending Logs',
                        '${trackingState.pendingLogs}',
                        null,
                      ),
                      if (trackingState.lastPosition?.speed != null) ...[
                        const Divider(),
                        _statusRow(
                          context,
                          'Speed',
                          '${(trackingState.lastPosition!.speed * 3.6).toStringAsFixed(1)} km/h',
                          null,
                        ),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Error message
            if (trackingState.error != null)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.red[50],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: Colors.red),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        trackingState.error!,
                        style: const TextStyle(color: Colors.red),
                      ),
                    ),
                  ],
                ),
              ),

            const Spacer(),

            // Active Duty Info
            activeDutyAsync.when(
              data: (duty) => duty.active && duty.session != null
                  ? Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Text(
                        'Duty started: ${duty.session!['started_at'] ?? 'N/A'}',
                        textAlign: TextAlign.center,
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    )
                  : const SizedBox.shrink(),
              loading: () => const SizedBox.shrink(),
              error: (_, _) => const SizedBox.shrink(),
            ),

            // Start/End Duty Button
            SizedBox(
              height: 56,
              child: FilledButton.icon(
                onPressed: () async {
                  final controller =
                      ref.read(gpsTrackingControllerProvider.notifier);
                  if (trackingState.isDutyActive) {
                    final confirm = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('End Duty?'),
                        content: const Text(
                          'GPS tracking will stop and your duty session will be recorded.',
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            child: const Text('Cancel'),
                          ),
                          FilledButton(
                            onPressed: () => Navigator.pop(ctx, true),
                            child: const Text('End Duty'),
                          ),
                        ],
                      ),
                    );
                    if (confirm == true) {
                      await controller.endDuty();
                    }
                  } else {
                    await controller.startDuty();
                  }
                },
                icon: Icon(
                  trackingState.isDutyActive
                      ? Icons.stop_circle
                      : Icons.play_circle,
                ),
                label: Text(
                  trackingState.isDutyActive ? 'End Duty' : 'Start Duty',
                  style: const TextStyle(fontSize: 18),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: trackingState.isDutyActive
                      ? Colors.red
                      : Colors.green,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _statusRow(
      BuildContext context, String label, String value, Color? valueColor) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: Theme.of(context).textTheme.bodyMedium),
          Text(
            value,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                  color: valueColor,
                ),
          ),
        ],
      ),
    );
  }
}
