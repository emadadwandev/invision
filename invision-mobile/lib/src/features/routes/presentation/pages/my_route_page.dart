import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/enums/route_status.dart';
import '../../../gps_tracking/presentation/providers/gps_tracking_providers.dart';
import '../../../../core/enums/visit_status.dart';
import '../../data/models/route_instance_model.dart';
import '../providers/route_providers.dart';

class MyRoutePage extends ConsumerWidget {
  const MyRoutePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final todayAsync = ref.watch(myRouteTodayProvider);

    return Scaffold(
      appBar: AppBar(title: const Text("Today's Route")),
      body: todayAsync.when(
        data: (instance) => instance == null
            ? const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.route, size: 64, color: Colors.grey),
                    SizedBox(height: 12),
                    Text('No route assigned for today.'),
                  ],
                ),
              )
            : _MyRouteBody(instance: instance),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _MyRouteBody extends ConsumerStatefulWidget {
  const _MyRouteBody({required this.instance});

  final RouteInstance instance;

  @override
  ConsumerState<_MyRouteBody> createState() => _MyRouteBodyState();
}

class _MyRouteBodyState extends ConsumerState<_MyRouteBody> {
  bool _loading = false;

  Future<void> _startRoute() async {
    setState(() => _loading = true);
    try {
      final repo = ref.read(routeRepositoryProvider);
      await repo.startRouteInstance(widget.instance.id);
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to start route: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _checkIn(StoreVisit visit) async {
    setState(() => _loading = true);
    try {
      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.getCurrentPosition();

      if (position == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('GPS unavailable. Please enable location services and try again.')),
          );
        }
        return;
      }

      final repo = ref.read(routeRepositoryProvider);
      await repo.checkIn(
        visit.id,
        latitude: position.latitude,
        longitude: position.longitude,
      );
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Check-in failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _checkOut(StoreVisit visit) async {
    setState(() => _loading = true);
    try {
      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.getCurrentPosition();

      if (position == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('GPS unavailable. Please enable location services and try again.')),
          );
        }
        return;
      }

      final repo = ref.read(routeRepositoryProvider);
      await repo.checkOut(
        visit.id,
        latitude: position.latitude,
        longitude: position.longitude,
      );
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Check-out failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _skipVisit(StoreVisit visit) async {
    final reason = await showDialog<String>(
      context: context,
      builder: (ctx) {
        final controller = TextEditingController();
        return AlertDialog(
          title: const Text('Skip Visit'),
          content: TextField(
            controller: controller,
            decoration: const InputDecoration(
              labelText: 'Reason',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(ctx, controller.text),
              child: const Text('Skip'),
            ),
          ],
        );
      },
    );

    if (reason == null || reason.isEmpty) return;

    setState(() => _loading = true);
    try {
      final repo = ref.read(routeRepositoryProvider);
      await repo.skipVisit(visit.id, reason: reason);
      ref.invalidate(myRouteTodayProvider);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Skip failed: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final instance = widget.instance;
    final theme = Theme.of(context);
    final isNotStarted = instance.status == RouteStatus.published;

    return Stack(
      children: [
        ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Summary Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      instance.routePlanName ?? 'Route #${instance.routePlanId}',
                      style: theme.textTheme.titleLarge,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        _StatChip(
                          label: 'Progress',
                          value:
                              '${instance.completionPercentage.toStringAsFixed(0)}%',
                        ),
                        const SizedBox(width: 8),
                        _StatChip(
                          label: 'Visits',
                          value:
                              '${instance.completedVisits}/${instance.totalVisits}',
                        ),
                        if (instance.totalDistanceKm != null) ...[
                          const SizedBox(width: 8),
                          _StatChip(
                            label: 'Distance',
                            value:
                                '${instance.totalDistanceKm!.toStringAsFixed(1)} km',
                          ),
                        ],
                      ],
                    ),
                    if (isNotStarted) ...[
                      const SizedBox(height: 12),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: _loading ? null : _startRoute,
                          icon: const Icon(Icons.play_arrow),
                          label: const Text('Start Route'),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Visits List
            Text('Store Visits', style: theme.textTheme.titleMedium),
            const SizedBox(height: 8),
            ...instance.visits.map(
              (visit) => _VisitCard(
                visit: visit,
                onCheckIn: () => _checkIn(visit),
                onCheckOut: () => _checkOut(visit),
                onSkip: () => _skipVisit(visit),
                loading: _loading,
              ),
            ),
          ],
        ),
        if (_loading)
          const Positioned.fill(
            child: ColoredBox(
              color: Color(0x44000000),
              child: Center(child: CircularProgressIndicator()),
            ),
          ),
      ],
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          Text(value,
              style: const TextStyle(
                  fontWeight: FontWeight.bold, fontSize: 14)),
          Text(label, style: const TextStyle(fontSize: 10)),
        ],
      ),
    );
  }
}

class _VisitCard extends StatelessWidget {
  const _VisitCard({
    required this.visit,
    required this.onCheckIn,
    required this.onCheckOut,
    required this.onSkip,
    required this.loading,
  });

  final StoreVisit visit;
  final VoidCallback onCheckIn;
  final VoidCallback onCheckOut;
  final VoidCallback onSkip;
  final bool loading;

  Color _statusColor(VisitStatus status) {
    return switch (status) {
      VisitStatus.pending => Colors.grey,
      VisitStatus.checkedIn => Colors.orange,
      VisitStatus.completed => Colors.green,
      VisitStatus.skipped => Colors.red,
    };
  }

  IconData _statusIcon(VisitStatus status) {
    return switch (status) {
      VisitStatus.pending => Icons.schedule,
      VisitStatus.checkedIn => Icons.login,
      VisitStatus.completed => Icons.check_circle,
      VisitStatus.skipped => Icons.cancel,
    };
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 14,
                  child: Text('${visit.visitOrder}',
                      style: const TextStyle(fontSize: 12)),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    visit.store?.name ?? 'Store #${visit.storeId}',
                    style: theme.textTheme.titleSmall,
                  ),
                ),
                Icon(
                  _statusIcon(visit.status),
                  color: _statusColor(visit.status),
                  size: 20,
                ),
                const SizedBox(width: 4),
                Text(
                  visit.status.name.toUpperCase(),
                  style: TextStyle(
                    color: _statusColor(visit.status),
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            if (visit.durationMinutes != null)
              Padding(
                padding: const EdgeInsets.only(top: 4, left: 38),
                child: Text(
                  'Duration: ${visit.durationMinutes} min',
                  style: theme.textTheme.bodySmall,
                ),
              ),
            if (visit.skipReason != null)
              Padding(
                padding: const EdgeInsets.only(top: 4, left: 38),
                child: Text(
                  'Reason: ${visit.skipReason}',
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: Colors.red,
                  ),
                ),
              ),
            // Action buttons
            if (visit.status == VisitStatus.pending ||
                visit.status == VisitStatus.checkedIn)
              Padding(
                padding: const EdgeInsets.only(top: 8, left: 38),
                child: Row(
                  children: [
                    if (visit.status == VisitStatus.pending) ...[
                      FilledButton.tonal(
                        onPressed: loading ? null : onCheckIn,
                        child: const Text('Check In'),
                      ),
                      const SizedBox(width: 8),
                      OutlinedButton(
                        onPressed: loading ? null : onSkip,
                        child: const Text('Skip'),
                      ),
                    ],
                    if (visit.status == VisitStatus.checkedIn)
                      FilledButton(
                        onPressed: loading ? null : onCheckOut,
                        child: const Text('Check Out'),
                      ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}
