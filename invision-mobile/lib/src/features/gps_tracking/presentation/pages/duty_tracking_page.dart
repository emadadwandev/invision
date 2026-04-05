import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/theme/app_theme.dart';
import '../providers/gps_tracking_providers.dart';

class DutyTrackingPage extends ConsumerWidget {
  const DutyTrackingPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final trackingState = ref.watch(gpsTrackingControllerProvider);
    final activeDutyAsync = ref.watch(activeDutyProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Duty Tracking',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: AppColors.onSurface)),
        backgroundColor: AppColors.surface.withOpacity(0.9),
        elevation: 0, scrolledUnderElevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Status Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: trackingState.isDutyActive
                      ? AppColors.secondary.withOpacity(0.4)
                      : AppColors.outlineVariant.withOpacity(0.5),
                ),
              ),
              child: Column(
                children: [
                  Icon(
                    trackingState.isDutyActive
                        ? Icons.gps_fixed_rounded
                        : Icons.gps_off_rounded,
                    size: 64,
                    color: trackingState.isDutyActive
                        ? AppColors.secondary
                        : AppColors.outline,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    trackingState.isDutyActive ? 'On Duty' : 'Off Duty',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: trackingState.isDutyActive
                              ? AppColors.secondary
                              : AppColors.onSurfaceVariant,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    trackingState.isDutyActive
                        ? 'GPS tracking is active'
                        : 'Start duty to begin GPS tracking',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: AppColors.onSurfaceVariant,
                        ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // GPS Stats
            if (trackingState.isDutyActive) ...[
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLowest,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.outlineVariant.withOpacity(0.4)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('GPS Status',
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(color: AppColors.onSurface, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 12),
                    _statusRow(context, 'Tracking',
                        trackingState.isTracking ? 'Active' : 'Paused',
                        trackingState.isTracking ? AppColors.secondary : AppColors.tertiary),
                    const Divider(color: AppColors.outlineVariant, height: 20),
                    _statusRow(context, 'Last Position',
                        trackingState.lastPosition != null
                            ? '${trackingState.lastPosition!.latitude.toStringAsFixed(6)}, ${trackingState.lastPosition!.longitude.toStringAsFixed(6)}'
                            : 'Acquiring...',
                        null),
                    const Divider(color: AppColors.outlineVariant, height: 20),
                    _statusRow(context, 'Pending Logs',
                        '${trackingState.pendingLogs}', null),
                    if (trackingState.lastPosition?.speed != null) ...[
                      const Divider(color: AppColors.outlineVariant, height: 20),
                      _statusRow(context, 'Speed',
                          '${(trackingState.lastPosition!.speed * 3.6).toStringAsFixed(1)} km/h',
                          null),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Error message
            if (trackingState.error != null)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.errorContainer,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline_rounded, color: AppColors.error),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(trackingState.error!,
                          style: const TextStyle(color: AppColors.error)),
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
            GestureDetector(
              onTap: () async {
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
                        TextButton(
                          onPressed: () => Navigator.pop(ctx, true),
                          child: const Text('End Duty'),
                        ),
                      ],
                    ),
                  );
                  if (confirm == true) await controller.endDuty();
                } else {
                  await controller.startDuty();
                }
              },
              child: Container(
                height: 56,
                decoration: BoxDecoration(
                  color: trackingState.isDutyActive ? AppColors.error : AppColors.secondary,
                  borderRadius: BorderRadius.circular(14),
                ),
                alignment: Alignment.center,
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      trackingState.isDutyActive
                          ? Icons.stop_circle_rounded
                          : Icons.play_circle_rounded,
                      color: Colors.white, size: 22,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      trackingState.isDutyActive ? 'End Duty' : 'Start Duty',
                      style: const TextStyle(
                          color: Colors.white, fontSize: 17, fontWeight: FontWeight.w700),
                    ),
                  ],
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
